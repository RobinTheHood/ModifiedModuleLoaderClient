<?php

declare(strict_types=1);

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient\ModuleManager;

use RobinTheHood\ModifiedModuleLoaderClient\Config;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\CombinationSatisfyerResult;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\DependencyBuilder;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\SystemSetFactory;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\ModuleLoader;

class ModuleManager
{
    /** @var ModuleInstaller */
    private $moduleInstaller;

    /** @var ModuleLoader */
    private $moduleLoader;

    /** @var LocalModuleLoader */
    private $localModuleLoader;

    /** @var DependencyBuilder */
    private $dependencyBuilder;

    /** @var SystemSetFactory */
    private $systemSetFactory;

    /** @var ModuleManagerLog */
    private $log;

    /** @var ModuleManagerLogger */
    private $logger;

    public static function create(int $mode): ModuleManager
    {
        $moduleInstaller = ModuleInstaller::create($mode);
        $moduleLoader = ModuleLoader::create($mode);
        $localModuleLoader = LocalModuleLoader::create($mode);
        $dependencyBuilder = DependencyBuilder::create($mode);
        $systemSetFactory = SystemSetFactory::create($mode);

        $moduleInstaller = new ModuleManager(
            $moduleInstaller,
            $moduleLoader,
            $localModuleLoader,
            $dependencyBuilder,
            $systemSetFactory
        );

        return $moduleInstaller;
    }

    public static function createFromConfig(): ModuleManager
    {
        return self::create(Config::getDependenyMode());
    }

    public function __construct(
        ModuleInstaller $moduleInstaller,
        ModuleLoader $moduleLoader,
        LocalModuleLoader $localModuleLoader,
        DependencyBuilder $dependencyBuilder,
        SystemSetFactory $systemSetFactory
    ) {
        $this->moduleInstaller = $moduleInstaller;
        $this->moduleLoader = $moduleLoader;
        $this->localModuleLoader = $localModuleLoader;
        $this->dependencyBuilder = $dependencyBuilder;
        $this->systemSetFactory = $systemSetFactory;

        $this->logger = new ModuleManagerNullLogger();
    }

    public function setLog(ModuleManagerLog $log)
    {
        $this->log = $log;
    }

    public function setLogger(ModuleManagerLoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    private function error(ModuleManagerMessage $message): ModuleManagerResult
    {
        $this->logger->error($message);
        return ModuleManagerResult::error($message);
    }

    private function info(ModuleManagerMessage $message): void
    {
        $this->logger->info($message);
    }

    /**
     * Lädt ein Modul vom Server herunter.
     */
    public function pull(string $archiveName, string $versionConstraint): ModuleManagerResult
    {
        if ($versionConstraint) {
            $module = $this->moduleLoader->loadLatestByArchiveNameAndConstraint($archiveName, $versionConstraint);
        } else {
            $module = $this->moduleLoader->loadLatestVersionByArchiveName($archiveName);
        }

        if (!$module) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::PULL_ERROR_MODULE_NOT_FOUND)
                ->setArchiveName($archiveName)
                ->setVersionConstraint($versionConstraint)
            );
        }

        if ($module->isLoaded()) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::PULL_ERROR_MODULE_ALLREADY_LOADED)
                ->setArchiveName($archiveName)
                ->setVersionConstraint($versionConstraint)
            );
        }

        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::PULL_INFO_START)
            ->setModule($module)
        );

        $module = $this->moduleInstaller->pull($module);
        return ModuleManagerResult::success()
            ->setModule($module);
    }

    /**
     * Löscht ein Modul das bereits heruntergeladen wurde.
     */
    public function delete(string $archiveName, string $version): ModuleManagerResult
    {
        $module = $this->localModuleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::DELETE_ERROR_MODULE_NOT_FOUND)
                ->setArchiveName($archiveName)
                ->setVersion($version)
            );

            // $this->log->error(
            //     self::ERROR_DELETE_MODULE_NOT_FOUND,
            //     "Can not delete %s, because module not found.",
            //     $archiveName,
            //     $version
            // );
            // throw new RuntimeException(
            //     "Can not delete module $archiveName version $version, because module not found."
            // );
        }

        if ($module->isInstalled()) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::DELETE_ERROR_MODULE_IS_INSTALED)
                ->setModule($module)
            );

            // $this->log->error(
            //     self::ERROR_DELETE_MODULE_IS_INSTALED,
            //     "Can not delete %s, because it is installed.",
            //     $module
            // );
            // throw new RuntimeException(
            //     "Can not delete module {$module->getArchiveName()} version {$module->getVersion()},"
            //     . " because it is installed"
            // );
        }

        // $this->log->write("Deleting %s ...", $module);
        // $this->moduleInstaller->delete($module, false);


        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::DELETE_INFO_START)
            ->setModule($module)
        );

        $this->moduleInstaller->delete($module, false);
        return ModuleManagerResult::success();
    }

    /**
     * Lädt und installiert ein Modul in das Shop System UND lädt und installiert alle Abhängigkeiten bzw.
     * abhängige Module nach.
     */
    public function install(string $archiveName, $versionConstraint): ModuleManagerResult
    {
        $moduleLoader = ModuleLoader::createFromConfig();
        $module = $moduleLoader->loadLatestByArchiveNameAndConstraint($archiveName, $versionConstraint);

        if (!$module) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::INSTALL_ERROR_MODULE_NOT_FOUND)
                //->setMessage("Can not install %s, because module not found.")
                ->setArchiveName($archiveName)
                ->setVersionConstraint($versionConstraint)
            );

            // $this->log->error(
            //     self::ERROR_INSTALL_MODULE_NOT_FOUND,
            //     "Can not install %s, because module not found.",
            //     $archiveName,
            //     $versionConstraint
            // );
            // throw new RuntimeException(
            //     "Can not install module $archiveName version $versionConstraint, because module not found."
            // );
        }

        $systemSet = $this->systemSetFactory->getSystemSet();

        $combinationSatisfyerResult = $this->dependencyBuilder->satisfies($archiveName, $versionConstraint, $systemSet);
        if (
            $combinationSatisfyerResult->result === CombinationSatisfyerResult::RESULT_COMBINATION_NOT_FOUND
            || !$combinationSatisfyerResult->foundCombination
        ) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::INSTALL_ERROR_MODULE_MISSING_REQUIREMENTS)
                //->setMessage("Can not install %s, because not all requirements are met.")
                ->setArchiveName($archiveName)
                ->setVersionConstraint($versionConstraint)
                ->setCombinationSatisfyerResult($combinationSatisfyerResult)
            );

            // $this->log->error(
            //     self::ERROR_INSTALL_MODULE_MISSING_REQUIREMENTS,
            //     "Can not install %s, because not all requirements are met. \n"
            //     . $combinationSatisfyerResult->failLog,
            //     $archiveName,
            //     $versionConstraint
            // );
            // throw new RuntimeException(
            //     "Can not install module $archiveName version $versionConstraint,"
            //     . " because not all requirements are met. \n"
            //     . $combinationSatisfyerResult->failLog
            // );
        }

        $version = $combinationSatisfyerResult->foundCombination->getVersion($archiveName);

        $module = $this->moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::INSTALL_ERROR_MODULE_NOT_FOUND)
                //->setMessage("Can not install %s, because module not found.")
                ->setArchiveName($archiveName)
                ->setVersionConstraint($versionConstraint)
            );

            // $this->log->error(
            //     self::ERROR_INSTALL_MODULE_NOT_FOUND,
            //     "Can not install %s, because module not found.",
            //     $archiveName,
            //     $version
            // );
            // throw new RuntimeException(
            //     "Can not delete install $archiveName version $version, because module not found."
            // );
        }

        if ($module->isInstalled()) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::INSTALL_ERROR_MODULE_ALLREADY_INSTALED)
                //->setMessage("Can not install %s, because it is already installed.")
                ->setModule($module)
            );

            // $this->log->error(
            //     self::ERROR_INSTALL_MODULE_ALLREADY_INSTALED,
            //     "Can not install %s, because it is already installed.",
            //     $module
            // );
            // throw new RuntimeException(
            //     "Can not install module {$module->getArchiveName()} version {$module->getVersion()},"
            //     . " because it is already installed."
            // );
        }

        if (!$module->isLoaded()) {
            $this->info(
                ModuleManagerMessage::create(ModuleManagerMessage::INSTALL_INFO_PULL_MODULE_START)
                ->setModule($module)
            );

            //$this->log->write("Downloding %s ...", $module);
            $module = $this->moduleInstaller->pull($module);
        }

        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::INSTALL_INFO_START)
            ->setModule($module)
        );
        //$this->log->write("Installing %s ...", $module);
        $this->moduleInstaller->install($module);

        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::INSTALL_INFO_UPDATE_AUTOLOAD_START)
        );
        //$this->log->write("Updating autotoload file");
        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();

        return ModuleManagerResult::success()
            ->setModule($module);
    }

    /**
     * Installiert ein Modul in das Shop System ABER lädt und installiert KEINE Abhängigkeiten / abhängige Module nach.
     * Sind nicht alle Abhängigkeiten erfüllt, wird nicht installiert und eine Exception geworfen.
     *
     * @param bool $skipDependencyCheck skip dependency check.
     */
    // public function installWithoutDependencies(
    //     string $archiveName,
    //     string $versionConstraint,
    //     bool $skipDependencyCheck = false
    // ): void {
    //     $this->moduleInstaller->installWithoutDependencies($module, $skipDependencyCheck, false);

    //     $autoloadFileCreator = new AutoloadFileCreator();
    //     $autoloadFileCreator->createAutoloadFile();
    // }

    /**
     * Aktuallisiert das Modul auf die neuste Version. Dabei werden keine Abhänggigkeiten
     * aktualisiert. Kommen durch das Update jedoch neue Abhänigkeiten hinzu, werden diese installt. Können nicht alle
     * Abhängigkeiten erfüllt werten, wird nicht aktualisiert und eine Exception geworfen.
     */
    public function update(string $archiveName): ModuleManagerResult
    {
        $moduleLoader = LocalModuleLoader::createFromConfig();
        $module = $moduleLoader->loadInstalledVersionByArchiveName($archiveName);

        if (!$module) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::UNINSTALL_ERROR_MODULE_NOT_FOUND)
                //->setMessage("Can not update %s, because module not found.")
                ->setArchiveName($archiveName)
            );

            // $this->log->error(
            //     self::ERROR_UNINSTALL_MODULE_NOT_FOUND,
            //     "Can not update %s, because module not found.",
            //     $archiveName
            // );
            // throw new RuntimeException(
            //     "Can not update update $archiveName, because module not found."
            // );
        }

        // $moduleText = "module $archiveName version {$module->getVersion()}";

        if (!$module->isInstalled()) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::UNINSTALL_ERROR_MODULE_NOT_INSTALLED)
                //->setMessage("Can not update %s, because module is not installed.")
                ->setModule($module)
            );

            // $this->log->error(
            //     self::ERROR_UNINSTALL_MODULE_NOT_INSTALLED,
            //     "Can not update %s, because module is not installed.",
            //     $module
            // );
            // throw new RuntimeException(
            //     "Can not update $moduleText, because module is not installed."
            // );
        }

        if ($module->isChanged()) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::UPDATE_ERROR_MODULE_IS_CHANGED)
                //->setMessage("Can not update %s, because module has changes.")
                ->setModule($module)
            );

            // $this->log->error(
            //     self::ERROR_UPDATE_MODULE_IS_CHANGED,
            //     "Can not update %s, because module has changes.",
            //     $module
            // );
            // throw new RuntimeException(
            //     "Can not update $moduleText, because module has changes."
            // );
        }

        //$this->log->write("Updating %s ...", $module);
        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::UPDATE_INFO_START)
            //->setMessage("Updating %s ...")
            ->setModule($module)
        );
        $newModule = $this->moduleInstaller->update($module, false);

        //$this->log->write("Updated to %s ...", $newModule);
        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::UPDATE_INFO_TO)
            //->setMessage("Updated to %s ...")
            ->setModule($newModule)
        );

        // $this->log->write("Updating autotoload file");
        // $autoloadFileCreator = new AutoloadFileCreator();
        // $autoloadFileCreator->createAutoloadFile();

        // return $newModule;

        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::UPDATE_INFO_UPDATE_AUTOLOAD_START)
            //->setMessage("Updating autotoload file")
        );
        //$this->log->write("Updating autotoload file");
        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();

        return ModuleManagerResult::success()
            ->setModule($newModule);
    }

    /**
     * Aktualiseirt NUR das Modul auf die neuste Version. Es werden keine fehlenden Abhänggigkeiten
     * installiert. Es werden keine Abhänggigkeiten aktualisiert. Können nicht alle Abhängigkeiten erfüllt werten,
     * wird nicht aktualisiert und eine Exception geworfen.
     */
    // public function updateWithoutMissingDependencies(string $archvieName, bool $skipDependencyCheck = false): Module
    // {
    //     $loadedNewModul = $this->moduleInstaller->updateWithoutMissingDependencies(
    //         $module,
    //         $skipDependencyCheck,
    //         false
    //     );

    //     $autoloadFileCreator = new AutoloadFileCreator();
    //     $autoloadFileCreator->createAutoloadFile();

    //     return $loadedNewModul;
    // }

    /**
     * Entfernt alle Änderungen die an den Modul-Dateien im Shop gemacht wurden. Änderungen an Template Dateien werden
     * nicht rückgängig gemacht.
     */
    public function discard(string $archiveName, bool $withTemplate = false): ModuleManagerResult
    {
        $moduleLoader = LocalModuleLoader::createFromConfig();
        $module = $moduleLoader->loadInstalledVersionByArchiveName($archiveName);

        if (!$module) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::DISCARD_ERROR_MODULE_NOT_FOUND)
                //->setMessage("Can not discard %s, because module not found.")
                ->setArchiveName($archiveName)
            );

            // $this->log->error(
            //     self::ERROR_DISCARD_MODULE_NOT_FOUND,
            //     "Can not discard %s, because module not found.",
            //     $archiveName
            // );
            // throw new RuntimeException("Can not discard $archiveName, because module not found.");
        }

        // $moduleText = "module $archiveName version {$module->getVersion()}";

        if (!$module->isChanged()) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::DISCARD_ERROR_MODULE_NOT_CHANGED)
                //->setMessage("Can not discard %s, because module not found.")
                ->setModule($module)
            );

            // $this->log->error(
            //     self::ERROR_DISCARD_MODULE_NOT_CHANGED,
            //     "Can not discard %s, because the modul has no changes.",
            //     $module
            // );
            // throw new RuntimeException("Can an not discard $moduleText, because the modul has no changes.\n");
        }

        //$this->log->write("Discarding %s ...", $module);
        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::DISCARD_INFO_START)
            //->setMessage("Discarding %s ...")
            ->setModule($module)
        );

        $this->moduleInstaller->discard($module, $withTemplate, false);
        return ModuleManagerResult::success();
    }

    /**
     * Deinstalliert nur das Modul, wenn es installiert und nicht mehr als abhänigkeit von einem anderen Modul benötigt
     * wird. Es werden keine Abhängigkeiten deinstalliert.
     *
     * Mit der force Option, kann der Abhängigkeits check übersprungen werden und das Modul wird trozdem deinstalliert.
     * Das kann aber zur folge haben, dass andere Module nicht mehr funktionieren.
     */
    public function uninstall(string $archiveName, bool $force = false): ModuleManagerResult
    {
        $moduleLoader = LocalModuleLoader::createFromConfig();
        $module = $moduleLoader->loadInstalledVersionByArchiveName($archiveName);

        if (!$module) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::UNINSTALL_ERROR_MODULE_NOT_FOUND)
                //->setMessage("Can not uninstall %s, because module not found.")
                ->setArchiveName($archiveName)
            );

            // $this->log->error(
            //     self::ERROR_UNINSTALL_MODULE_NOT_FOUND,
            //     "Can not uninstall %s, because module not found.",
            //     $archiveName
            // );
            // throw new RuntimeException(
            //     "Can not uninstall $archiveName, because module not found."
            // );
        }

        if (!$module->isInstalled()) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::UNINSTALL_ERROR_MODULE_NOT_INSTALLED)
                //->setMessage("Can not uninstall %s, because module is not installed.")
                ->setModule($module)
            );

            // $this->log->error(
            //     self::ERROR_UNINSTALL_MODULE_NOT_INSTALLED,
            //     "Can not uninstall %s, because module is not installed.",
            //     $module
            // );
            // throw new RuntimeException(
            //     "Can not uninstall module {$module->getArchiveName()} version {$module->getVersion()},"
            //     . " because module is not installed."
            // );
        }

        if ($module->isChanged() && $force === false) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::UNINSTALL_ERROR_MODULE_IS_CHANGED)
                //->setMessage("Can not uninstall %s, because module has changes.")
                ->setModule($module)
            );

            // $this->log->error(
            //     self::ERROR_UNINSTALL_MODULE_IS_CHANGED,
            //     "Can not uninstall %s, because module has changes.",
            //     $module
            // );
            // throw new RuntimeException(
            //     "Can not uninstall module {$module->getArchiveName()} version {$module->getVersion()},"
            //     . " because module has changes."
            // );
        }

        if ($module->getUsedBy() && $force === false) {
            // $subModulesArchiveNames = [];
            // foreach ($module->getUsedBy() as $subModule) {
            //     $subModulesArchiveNames[] .= $subModule->getArchiveName();
            // }
            // $usedBy = implode("\n", $subModulesArchiveNames);

            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::UNINSTALL_ERROR_MODULE_IS_USED_BY)
                //->setMessage("Can not uninstall %s, because module is used by other modules.")
                ->setModule($module)
            );

            // $this->log->error(
            //     self::ERROR_UNINSTALL_MODULE_IS_CHANGED,
            //     "Can not uninstall %s, because module is used by other modules.\n$usedBy",
            //     $module
            // );
            // throw new RuntimeException(
            //     "Can not uninstall module {$module->getArchiveName()} version {$module->getVersion()},"
            //     . " because module has changes."
            // );
        }

        //$this->log->write("Uninstalling %s ...", $module);
        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::UNINSTALL_INFO_START)
            //->setMessage("Uninstalling %s ...")
            ->setModule($module)
        );

        $this->moduleInstaller->uninstall($module, $force);

        // $this->log->write("Updating autotoload file");

        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::UNINSTALL_INFO_UPDATE_AUTOLOAD_START)
            //->setMessage("Updating autotoload file")
        );
        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();

        return ModuleManagerResult::success();
    }
}
