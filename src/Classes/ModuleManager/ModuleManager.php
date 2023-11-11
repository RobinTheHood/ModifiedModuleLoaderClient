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
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RuntimeException;

class ModuleManager
{
    private const ERROR_PULL_MODULE_NOT_FOUND = 101;
    private const ERROR_PULL_MODULE_ALLREADY_LOADED = 102;

    private const ERROR_DELETE_MODULE_NOT_FOUND = 201;
    private const ERROR_DELETE_MODULE_IS_INSTALED = 202;

    private const ERROR_INSTALL_MODULE_NOT_FOUND = 301;
    private const ERROR_INSTALL_MODULE_MISSING_REQUIREMENTS = 302;
    private const ERROR_INSTALL_MODULE_ALLREADY_INSTALED = 303;

    private const ERROR_UPDATE_MODULE_NOT_FOUND = 401;
    private const ERROR_UPDATE_MODULE_NOT_INSTALLED = 402;
    private const ERROR_UPDATE_MODULE_IS_CHANGED = 403;

    private const ERROR_DISCARD_MODULE_NOT_FOUND = 501;
    private const ERROR_DISCARD_MODULE_NOT_CHANGED = 502;

    private const ERROR_UNINSTALL_MODULE_NOT_FOUND = 601;
    private const ERROR_UNINSTALL_MODULE_NOT_INSTALLED = 602;
    private const ERROR_UNINSTALL_MODULE_IS_CHANGED = 603;

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
    }

    public function setLog(ModuleManagerLog $log)
    {
        $this->log = $log;
    }

    /**
     * Lädt ein Modul vom Server herunter.
     */
    public function pull(string $archiveName, string $versionConstraint): Module
    {
        if ($versionConstraint) {
            $module = $this->moduleLoader->loadLatestByArchiveNameAndConstraint($archiveName, $versionConstraint);
        } else {
            $module = $this->moduleLoader->loadLatestVersionByArchiveName($archiveName);
        }

        if (!$module) {
            $this->log->error(
                self::ERROR_PULL_MODULE_NOT_FOUND,
                "Can not pull %s, because module not found.",
                $archiveName,
                $versionConstraint
            );
            throw new RuntimeException(
                "Can not pull module $archiveName version $versionConstraint, because module not found."
            );
        }

        if ($module->isLoaded()) {
            $this->log->error(
                self::ERROR_PULL_MODULE_ALLREADY_LOADED,
                "Can not pull %s, because it is already downloaded.",
                $module
            );
            throw new RuntimeException(
                "Can not pull module {$module->getArchiveName()} version {$module->getVersion()},"
                . " because it is already downloaded."
            );
        }

        $this->log->write("Downloading %s ...", $module);

        return $this->moduleInstaller->pull($module);
    }

    /**
     * Löscht ein Modul das bereits heruntergeladen wurde.
     */
    public function delete(string $archiveName, string $version): void
    {
        $module = $this->localModuleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            $this->log->error(
                self::ERROR_DELETE_MODULE_NOT_FOUND,
                "Can not delete %s, because module not found.",
                $archiveName,
                $version
            );
            throw new RuntimeException(
                "Can not delete module $archiveName version $version, because module not found."
            );
        }

        if ($module->isInstalled()) {
            $this->log->error(
                self::ERROR_DELETE_MODULE_IS_INSTALED,
                "Can not delete %s, because it is installed.",
                $module
            );
            throw new RuntimeException(
                "Can not delete module {$module->getArchiveName()} version {$module->getVersion()},"
                . " because it is installed"
            );
        }

        $this->log->write("Deleting %s ...", $module);

        $this->moduleInstaller->delete($module, false);
    }

    /**
     * Lädt und installiert ein Modul in das Shop System UND lädt und installiert alle Abhängigkeiten bzw.
     * abhängige Module nach.
     */
    public function install(string $archiveName, $versionConstraint): void
    {
        $systemSet = $this->systemSetFactory->getSystemSet();

        $combinationSatisfyerResult = $this->dependencyBuilder->satisfies($archiveName, $versionConstraint, $systemSet);
        if (
            $combinationSatisfyerResult->result === CombinationSatisfyerResult::RESULT_COMBINATION_NOT_FOUND
            || !$combinationSatisfyerResult->foundCombination
        ) {
            $this->log->error(
                self::ERROR_INSTALL_MODULE_MISSING_REQUIREMENTS,
                "Can not install %s, because not all requirements are met. "
                . print_r($combinationSatisfyerResult->testCombination),
                $archiveName,
                $versionConstraint
            );
            throw new RuntimeException(
                "Can not install module $archiveName version $versionConstraint, because not all requirements are met."
                . print_r($combinationSatisfyerResult->testCombination)
            );
        }

        $version = $combinationSatisfyerResult->foundCombination->getVersion($archiveName);

        $module = $this->moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            $this->log->error(
                self::ERROR_INSTALL_MODULE_NOT_FOUND,
                "Can not install %s, because module not found.",
                $archiveName,
                $version
            );
            throw new RuntimeException(
                "Can not delete install $archiveName version $version, because module not found."
            );
        }

        if ($module->isInstalled()) {
            $this->log->error(
                self::ERROR_INSTALL_MODULE_ALLREADY_INSTALED,
                "Can not install %s, because it is already installed.",
                $module
            );
            throw new RuntimeException(
                "Can not install module {$module->getArchiveName()} version {$module->getVersion()},"
                . " because it is already installed."
            );
        }

        if (!$module->isLoaded()) {
            $this->log->write("Downloding %s ...", $module);
            $module = $this->moduleInstaller->pull($module);
        }

        $this->log->write("Installing %s ...", $module);
        $this->moduleInstaller->install($module);

        $this->log->write("Updating autotoload file");
        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();
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
    public function update(string $archiveName): Module
    {
        $moduleLoader = LocalModuleLoader::createFromConfig();
        $module = $moduleLoader->loadInstalledVersionByArchiveName($archiveName);

        if (!$module) {
            $this->log->error(
                self::ERROR_UNINSTALL_MODULE_NOT_FOUND,
                "Can not update %s, because module not found.",
                $archiveName
            );
            throw new RuntimeException(
                "Can not update update $archiveName, because module not found."
            );
        }

        $moduleText = "module $archiveName version {$module->getVersion()}";

        if (!$module->isInstalled()) {
            $this->log->error(
                self::ERROR_UNINSTALL_MODULE_NOT_INSTALLED,
                "Can not update %s, because module is not installed.",
                $module
            );
            throw new RuntimeException(
                "Can not update $moduleText, because module is not installed."
            );
        }

        if ($module->isChanged()) {
            $this->log->error(
                self::ERROR_UPDATE_MODULE_IS_CHANGED,
                "Can not update %s, because module has changes.",
                $module
            );
            throw new RuntimeException(
                "Can not update $moduleText, because module has changes."
            );
        }

        $this->log->write("Updating %s ...", $module);

        $newModule = $this->moduleInstaller->update($module, false);

        $this->log->write("Updated to %s ...", $newModule);

        $this->log->write("Updating autotoload file");
        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();

        return $newModule;
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
    public function discard(string $archiveName, bool $withTemplate = false): void
    {
        $moduleLoader = LocalModuleLoader::createFromConfig();
        $module = $moduleLoader->loadInstalledVersionByArchiveName($archiveName);

        if (!$module) {
            $this->log->error(
                self::ERROR_DISCARD_MODULE_NOT_FOUND,
                "Can not discard %s, because module not found.",
                $archiveName
            );
            throw new RuntimeException("Can not discard $archiveName, because module not found.");
        }

        $moduleText = "module $archiveName version {$module->getVersion()}";

        if (!$module->isChanged()) {
            $this->log->error(
                self::ERROR_DISCARD_MODULE_NOT_CHANGED,
                "Can not discard %s, because the modul has no changes.",
                $module
            );
            throw new RuntimeException("Can an not discard $moduleText, because the modul has no changes.\n");
        }

        $this->log->write("Discarding %s ...", $module);
        $this->moduleInstaller->discard($module, $withTemplate, false);
    }

    /**
     * Deinstalliert nur das Modul, wenn es installiert und nicht mehr als abhänigkeit von einem anderen Modul benötigt
     * wird. Es werden keine Abhängigkeiten deinstalliert.
     *
     * Mit der force Option, kann der Abhängigkeits check übersprungen werden und das Modul wird trozdem deinstalliert.
     * Das kann aber zur folge haben, dass andere Module nicht mehr funktionieren.
     */
    public function uninstall(string $archiveName, bool $force = false): bool
    {
        $moduleLoader = LocalModuleLoader::createFromConfig();
        $module = $moduleLoader->loadInstalledVersionByArchiveName($archiveName);

        if (!$module) {
            $this->log->error(
                self::ERROR_UNINSTALL_MODULE_NOT_FOUND,
                "Can not uninstall %s, because module not found.",
                $archiveName
            );
            throw new RuntimeException(
                "Can not uninstall install $archiveName, because module not found."
            );
        }

        if (!$module->isInstalled()) {
            $this->log->error(
                self::ERROR_UNINSTALL_MODULE_NOT_INSTALLED,
                "Can not uninstall %s, because module is not installed.",
                $module
            );
            throw new RuntimeException(
                "Can not uninstall module {$module->getArchiveName()} version {$module->getVersion()},"
                . " because module is not installed."
            );
        }

        if ($module->isChanged() && $force === false) {
            $this->log->error(
                self::ERROR_UNINSTALL_MODULE_IS_CHANGED,
                "Can not uninstall %s, because module has changes.",
                $module
            );
            throw new RuntimeException(
                "Can not uninstall module {$module->getArchiveName()} version {$module->getVersion()},"
                . " because module has changes."
            );
        }

        if ($module->getUsedBy() && $force === false) {
            $subModulesArchiveNames = [];
            foreach ($module->getUsedBy() as $subModule) {
                $subModulesArchiveNames[] .= $subModule->getArchiveName();
            }
            $usedBy = implode("\n", $subModulesArchiveNames);
            $this->log->error(
                self::ERROR_UNINSTALL_MODULE_IS_CHANGED,
                "Can not uninstall %s, because module is used by other modules.\n$usedBy",
                $module
            );
            throw new RuntimeException(
                "Can not uninstall module {$module->getArchiveName()} version {$module->getVersion()},"
                . " because module has changes."
            );
        }

        $this->log->write("Uninstalling %s ...", $module);
        $this->moduleInstaller->uninstall($module, $force);

        $this->log->write("Updating autotoload file");
        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();

        return true;
    }
}
