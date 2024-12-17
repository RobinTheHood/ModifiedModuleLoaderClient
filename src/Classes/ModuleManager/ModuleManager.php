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

use Exception;
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

    /** @var ModuleManagerLoggerInterface */
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
        }

        if ($module->isInstalled()) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::DELETE_ERROR_MODULE_IS_INSTALED)
                ->setModule($module)
            );
        }

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
                ->setArchiveName($archiveName)
                ->setVersionConstraint($versionConstraint)
            );
        }

        $systemSet = $this->systemSetFactory->getSystemSet();

        $combinationSatisfyerResult = $this->dependencyBuilder->satisfies($archiveName, $versionConstraint, $systemSet);
        if (
            $combinationSatisfyerResult->result === CombinationSatisfyerResult::RESULT_COMBINATION_NOT_FOUND
            || !$combinationSatisfyerResult->foundCombination
        ) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::INSTALL_ERROR_MODULE_MISSING_REQUIREMENTS)
                ->setArchiveName($archiveName)
                ->setVersionConstraint($versionConstraint)
                ->setCombinationSatisfyerResult($combinationSatisfyerResult)
            );
        }

        $version = $combinationSatisfyerResult->foundCombination->getVersion($archiveName);

        $module = $this->moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::INSTALL_ERROR_MODULE_NOT_FOUND)
                ->setArchiveName($archiveName)
                ->setVersionConstraint($versionConstraint)
            );
        }

        if ($module->isInstalled()) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::INSTALL_ERROR_MODULE_ALLREADY_INSTALED)
                ->setModule($module)
            );
        }

        if (!$module->isLoaded()) {
            $this->info(
                ModuleManagerMessage::create(ModuleManagerMessage::INSTALL_INFO_PULL_MODULE_START)
                ->setModule($module)
            );
            $module = $this->moduleInstaller->pull($module);
        }

        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::INSTALL_INFO_START)
            ->setModule($module)
        );
        $this->moduleInstaller->install($module);

        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::INSTALL_INFO_UPDATE_AUTOLOAD_START)
        );

        $moduleManagerResult = $this->createAutoloadFile();
        if ($moduleManagerResult->getType() === ModuleManagerResult::TYPE_ERROR) {
            return $moduleManagerResult;
        }

        return ModuleManagerResult::success()
            ->setModule($module);
    }

    /**
     * Installiert ein Modul in das Shop System ABER lädt und installiert KEINE Abhängigkeiten / abhängige Module nach.
     *
     * @param bool $skipDependencyCheck skip dependency check.
     */
    public function installWithoutDependencies(
        string $archiveName,
        string $versionConstraint,
        bool $skipDependencyCheck = false
    ): ModuleManagerResult {
        $moduleLoader = ModuleLoader::createFromConfig();
        $module = $moduleLoader->loadLatestByArchiveNameAndConstraint($archiveName, $versionConstraint);

        if (!$module) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::INSTALL_ERROR_MODULE_NOT_FOUND)
                ->setArchiveName($archiveName)
                ->setVersionConstraint($versionConstraint)
            );
        }

        if ($skipDependencyCheck === false) {
            $systemSet = $this->systemSetFactory->getSystemSet();

            $combinationSatisfyerResult
                = $this->dependencyBuilder->satisfies($archiveName, $versionConstraint, $systemSet);
            if (
                $combinationSatisfyerResult->result === CombinationSatisfyerResult::RESULT_COMBINATION_NOT_FOUND
                || !$combinationSatisfyerResult->foundCombination
            ) {
                return $this->error(
                    ModuleManagerMessage::create(ModuleManagerMessage::INSTALL_ERROR_MODULE_MISSING_REQUIREMENTS)
                    ->setArchiveName($archiveName)
                    ->setVersionConstraint($versionConstraint)
                    ->setCombinationSatisfyerResult($combinationSatisfyerResult)
                );
            }

            $version = $combinationSatisfyerResult->foundCombination->getVersion($archiveName);

            $module = $this->moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

            if (!$module) {
                return $this->error(
                    ModuleManagerMessage::create(ModuleManagerMessage::INSTALL_ERROR_MODULE_NOT_FOUND)
                    ->setArchiveName($archiveName)
                    ->setVersionConstraint($versionConstraint)
                );
            }
        }

        if ($module->isInstalled()) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::INSTALL_ERROR_MODULE_ALLREADY_INSTALED)
                ->setModule($module)
            );
        }

        if (!$module->isLoaded()) {
            $this->info(
                ModuleManagerMessage::create(ModuleManagerMessage::INSTALL_INFO_PULL_MODULE_START)
                ->setModule($module)
            );
            $module = $this->moduleInstaller->pull($module);
        }

        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::INSTALL_INFO_START)
            ->setModule($module)
        );
        $this->moduleInstaller->installWithoutDependencies($module, true, true);

        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::INSTALL_INFO_UPDATE_AUTOLOAD_START)
        );

        $moduleManagerResult = $this->createAutoloadFile();
        if ($moduleManagerResult->getType() === ModuleManagerResult::TYPE_ERROR) {
            return $moduleManagerResult;
        }

        return ModuleManagerResult::success()
            ->setModule($module);
    }

    /**
     * Aktuallisiert das Modul auf die neuste mögliche Version. Dabei werden keine Abhänggigkeiten
     * aktualisiert. Kommen durch das Update jedoch neue Abhängigkeiten hinzu, werden diese installt. Können nicht alle
     * Abhängigkeiten erfüllt werten, wird nicht aktualisiert und eine Exception geworfen.
     */
    public function update(string $archiveName): ModuleManagerResult
    {
        $moduleLoader = LocalModuleLoader::createFromConfig();
        $module = $moduleLoader->loadInstalledVersionByArchiveName($archiveName);

        if (!$module) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::UPDATE_ERROR_MODULE_NOT_FOUND)
                ->setArchiveName($archiveName)
            );
        }

        if (!$module->isInstalled()) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::UPDATE_ERROR_MODULE_NOT_INSTALLED)
                ->setModule($module)
            );
        }

        if ($module->isChanged()) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::UPDATE_ERROR_MODULE_IS_CHANGED)
                ->setModule($module)
            );
        }

        $systemSet = $this->systemSetFactory->getSystemSet();
        $versionConstraint = '>' . $module->getVersion();
        $combinationSatisfyerResult = $this->dependencyBuilder->satisfies($archiveName, $versionConstraint, $systemSet);
        if (
            $combinationSatisfyerResult->result === CombinationSatisfyerResult::RESULT_COMBINATION_NOT_FOUND
            || !$combinationSatisfyerResult->foundCombination
        ) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::UPDATE_ERROR_MODULE_MISSING_REQUIREMENTS)
                ->setArchiveName($archiveName)
                ->setVersionConstraint($versionConstraint)
                ->setCombinationSatisfyerResult($combinationSatisfyerResult)
            );
        }

        $version = $combinationSatisfyerResult->foundCombination->getVersion($archiveName);
        $newModule = $this->moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$newModule) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::UPDATE_ERROR_MODULE_NOT_FOUND)
                ->setArchiveName($archiveName)
                ->setVersion($version)
            );
        }

        if (!$newModule->isLoaded()) {
            $this->info(
                ModuleManagerMessage::create(ModuleManagerMessage::UDPATE_INFO_PULL_MODULE_START)
                ->setModule($newModule)
            );
            $newModule = $this->moduleInstaller->pull($newModule);
        }

        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::UPDATE_INFO_START)
            ->setModule($module)
        );
        $this->moduleInstaller->update($module, $newModule, false);

        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::UPDATE_INFO_TO)
            ->setModule($newModule)
        );

        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::UPDATE_INFO_UPDATE_AUTOLOAD_START)
        );

        $moduleManagerResult = $this->createAutoloadFile();
        if ($moduleManagerResult->getType() === ModuleManagerResult::TYPE_ERROR) {
            return $moduleManagerResult;
        }

        return ModuleManagerResult::success()
            ->setModule($newModule);
    }

    /**
     * Aktualiseirt NUR das Modul auf die neuste möglche Version. Es werden keine fehlenden Abhänggigkeiten
     * installiert. Es werden keine Abhänggigkeiten aktualisiert. Können nicht alle Abhängigkeiten erfüllt werten,
     * wird nicht aktualisiert, es sei denn, der Abhängigkeits-Check wird mit $skipDependencyCheck deaktiviert.
     */
    public function updateWithoutDependencies(
        string $archiveName,
        bool $skipDependencyCheck = false
    ): ModuleManagerResult {
        $moduleLoader = LocalModuleLoader::createFromConfig();
        $module = $moduleLoader->loadInstalledVersionByArchiveName($archiveName);

        if (!$module) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::UPDATE_ERROR_MODULE_NOT_FOUND)
                ->setArchiveName($archiveName)
            );
        }

        if (!$module->isInstalled()) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::UPDATE_ERROR_MODULE_NOT_INSTALLED)
                ->setModule($module)
            );
        }

        if ($module->isChanged()) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::UPDATE_ERROR_MODULE_IS_CHANGED)
                ->setModule($module)
            );
        }

        if ($skipDependencyCheck === false) {
            $systemSet = $this->systemSetFactory->getSystemSet();
            $versionConstraint = '>' . $module->getVersion();
            $combinationSatisfyerResult =
                $this->dependencyBuilder->satisfies($archiveName, $versionConstraint, $systemSet);
            if (
                $combinationSatisfyerResult->result === CombinationSatisfyerResult::RESULT_COMBINATION_NOT_FOUND
                || !$combinationSatisfyerResult->foundCombination
            ) {
                return $this->error(
                    ModuleManagerMessage::create(ModuleManagerMessage::UPDATE_ERROR_MODULE_MISSING_REQUIREMENTS)
                    ->setArchiveName($archiveName)
                    ->setVersionConstraint($versionConstraint)
                    ->setCombinationSatisfyerResult($combinationSatisfyerResult)
                );
            }
            $version = $combinationSatisfyerResult->foundCombination->getVersion($archiveName);
            $newModule = $this->moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);
        } else {
            $newModule = $this->moduleLoader->loadLatestVersionByArchiveName($archiveName);
            $version = '';
        }

        if (!$newModule) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::UPDATE_ERROR_MODULE_NOT_FOUND)
                ->setArchiveName($archiveName)
                ->setVersion($version)
            );
        }

        if (!$newModule->isLoaded()) {
            $this->info(
                ModuleManagerMessage::create(ModuleManagerMessage::UDPATE_INFO_PULL_MODULE_START)
                ->setModule($newModule)
            );
            $newModule = $this->moduleInstaller->pull($newModule);
        }

        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::UPDATE_INFO_START)
            ->setModule($module)
        );
        $this->moduleInstaller->updateWithoutDependencies($module, $newModule, true, true);

        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::UPDATE_INFO_TO)
            ->setModule($newModule)
        );

        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::UPDATE_INFO_UPDATE_AUTOLOAD_START)
        );

        $moduleManagerResult = $this->createAutoloadFile();
        if ($moduleManagerResult->getType() == ModuleManagerResult::TYPE_ERROR) {
            return $moduleManagerResult;
        }

        return ModuleManagerResult::success()
            ->setModule($newModule);
    }

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
                ->setArchiveName($archiveName)
            );
        }

        if (!$module->isChanged()) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::DISCARD_ERROR_MODULE_NOT_CHANGED)
                ->setModule($module)
            );
        }

        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::DISCARD_INFO_START)
            ->setModule($module)
        );

        $this->moduleInstaller->discard($module, $withTemplate, false);
        return ModuleManagerResult::success()
            ->setModule($module);
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
                ->setArchiveName($archiveName)
            );
        }

        if (!$module->isInstalled()) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::UNINSTALL_ERROR_MODULE_NOT_INSTALLED)
                ->setModule($module)
            );
        }

        if ($module->isChanged() && $force === false) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::UNINSTALL_ERROR_MODULE_IS_CHANGED)
                ->setModule($module)
            );
        }

        if ($module->getUsedBy() && $force === false) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::UNINSTALL_ERROR_MODULE_IS_USED_BY)
                ->setModule($module)
            );
        }

        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::UNINSTALL_INFO_START)
            ->setModule($module)
        );

        $this->moduleInstaller->uninstall($module, $force);

        $this->info(
            ModuleManagerMessage::create(ModuleManagerMessage::UNINSTALL_INFO_UPDATE_AUTOLOAD_START)
        );

        $moduleManagerResult = $this->createAutoloadFile();
        if ($moduleManagerResult->getType() === ModuleManagerResult::TYPE_ERROR) {
            return $moduleManagerResult;
        }

        return ModuleManagerResult::success()
            ->setModule($module);
    }

    public function createAutoloadFile(): ModuleManagerResult
    {
        try {
            $autoloadFileCreator = new AutoloadFileCreator();
            $autoloadFileCreator->createAutoloadFile();
        } catch (Exception $e) {
            return $this->error(
                ModuleManagerMessage::create(ModuleManagerMessage::AUTOLOAD_ERROR_CAN_NOT_CREATE_AUTOLOAD_FILE)
                ->setMessage($e->getMessage())
            );
        }

        return ModuleManagerResult::success();
    }
}
