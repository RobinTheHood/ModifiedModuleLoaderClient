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

namespace RobinTheHood\ModifiedModuleLoaderClient;

use RobinTheHood\ModifiedModuleLoaderClient\Archive\ArchiveHandler;
use RobinTheHood\ModifiedModuleLoaderClient\Archive\ArchivePuller;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\DependencyManager;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;

class ModuleInstaller
{
    public static function create(int $mode): ModuleInstaller
    {
        $dependencyManager = DependencyManager::create($mode);
        $moduleFilter = ModuleFilter::create($mode);
        $localModuleLoader = LocalModuleLoader::create($mode);
        $archivePuller = ArchivePuller::create();
        $archiveHandler = ArchiveHandler::create($mode);
        $moduleInstaller = new ModuleInstaller(
            $dependencyManager,
            $moduleFilter,
            $localModuleLoader,
            $archivePuller,
            $archiveHandler
        );
        return $moduleInstaller;
    }

    public static function createFromConfig(): ModuleInstaller
    {
        return self::create(Config::getDependenyMode());
    }

    public function __construct()
    {
    }

    /**
     * Lädt ein Modul (archiveName, Version) herunter.
     *
     *
     * Mögliche Fehler und Exceptions
     * - Keine Verbindung zum Server - NoServerConnectionException extends PullException
     * - Nicht genügent Speicherplatz - InsufficientDiskSpaceException extends PullException
     * - Zip Datei kann nicht entpackt werden - UnzipFailedException extends PullException
     * - Keine Schreibrechte für Archives - NoWritePermissionForArchivesException extends PullException
     * - Keine Schreibrechte für Modules - NoWritePermissionForModulesException extends PullException
     * - Das Modul ist bereits geladen - ModuleAlreadyLoadedException extends PullException
     */
    public function pull(Module $module)
    {
    }

    /**
     * Löscht ein Modul (archiveName, Version) das bereits heruntergeladen wurde.
     *
     * Mögliche Fehler und Exceptions
     * - Das Modul ist noch installiert und kann deswegen nicht gelöscht werden
     * - Keine Schreibrechte für Modules
     *
     * Fragen
     * - Was passiert, wenn nicht alle Datein gelöscht werden konnten, dann ist das Modul in einem defekten Zustand
     */
    public function delete(Module $module)
    {
    }

    /**
     * Installiert ein Modul (archiveName, Version) in das Shop System. ABER lädt und installiert keine Abhänigkeiten /
     * abhängige Module nach.
     *
     * Mögliche Fehler und Exceptions
     * - alle Fehler aus internalInstall()
     * - Nicht alle Abhängigkeiten sind vorhanden
     * - Keine Schreibrechte im Shop
     * - Nicht alle Datein konnten im Shop installiert werden.
     * - Autoload kann nicht aktuallisiert werden
     * - Das Modull ist bereits installiert
     */
    public function install(Module $module, bool $force = false): void
    {
    }

    /**
     * Installiert ein Modul (archiveName, Version) in das Shop System. UND lädt und installiert alle Abhänigketen /
     * abhängige Module nach.
     *
     * Mögliche Fehler und Exceptions
     * - alle Fehler aus internalInstall()
     * - alle Fehler aus internalInstallDependencies()
     * - Keine passende kombination an Abhänigkeiten gefunden
     * - Autoload kann nicht aktuallisiert werden
     * - Das Modull ist bereits installiert
     */
    public function installWithDependencies(Module $module): void
    {
    }

    /**
     * (Re-) Installiert / Überschreibt ein Modul (archive, Version) ohne dabei auf Abhänigkeiten und Modulstatus zu
     * achten. Es wird nur auf Dateiebene kontrolliert, ob alle Dateien geschrieben werden konnten. Die Autoload Datei
     * wird NICHT erzeugt / erneuert.
     */
    private function internalInstall(Module $module): void
    {
        $this->installFiles($module);
        $this->createHashFile($module);
    }

    /**
     * (Re-) Installiert / Überschreibt nur die Datei zu einem Modul (archive, Version). Es wird nur auf Datei-Ebene
     * kontrolliert, ob alle Dateien geschrieben werden konnten. Die `modulehash.json` Datei wird NICHT erzeugt /
     * erneuert.
     */
    private function installFiles(Module $module): void
    {
        // Install Source Files to Shop Root
        $files = $module->getSrcFilePaths();

        foreach ($files as $file) {
            $src = $module->getLocalRootPath() . $module->getSrcRootPath() . '/' . $file;

            $files = $module->getTemplateFiles($file);
            foreach ($files as $file) {
                $overwrite = false;
                if (!FileInfo::isTemplateFile($file)) {
                    $overwrite = true;
                }

                $file = ModulePathMapper::moduleSrcToShopRoot($file);

                $dest = App::getShopRoot() . $file;
                $this->installFile($src, $dest, $overwrite);
            }
        }

        // Install Source Mmlc Files to shop vendor-mmlc
        $files = $module->getSrcMmlcFilePaths();
        foreach ($files as $file) {
            $src = $module->getLocalRootPath() . $module->getSrcMmlcRootPath() . '/' . $file;
            $file = ModulePathMapper::moduleSrcMmlcToShopVendorMmlc($file, $module->getArchiveName());
            $dest = App::getShopRoot() . '/' . $file;
            $this->installFile($src, $dest, true);
        }
    }

    /**
     * Erzeugt / Überschreibt die `modulehash.json zu einem Modul (archive, Version)` Es wird nur auf Datei-Ebene
     * kontrolliert, ob alle Dateien geschrieben werden konnten.
     */
    private function createHashFile(Module $module): void
    {
        $moduleHashFileCreator = new ModuleHashFileCreator();
        $moduleHashFile = $moduleHashFileCreator->createHashFile($module);
        $moduleHashFile->writeTo($module->getHashPath());
    }

    /**
     * Lädt und installiert
     */
    private function internalPullAndInstall(Module $module): void
    {
        if (!$module->isLoaded()) {
            $this->pull($module);
        }

        // TODO: Rename to reloadModule
        $reloaded = $this->reload($module);

        if (!$reloaded->isLoaded()) {
            $message =
                "Can not pull and install module {$module->getArchiveName()} {$module->getVersion()}. "
                . "Module is not loaded.";
            StaticLogger::log(LogLevel::WARNING, $message);
            // NOTE: Vielleicht neue class ModuleOperationException hinzufügen
            throw new RuntimeException($message);
        }

        if ($reloaded->isInstalled()) {
            return;
        }

        $this->uninstall($module);
        $this->internalInstall($module);
    }

    private function internalInstallDependencies(Module $parentModule, Combination $combination): void
    {
        $modules = $this->dependencyManager->getAllModulesFromCombination($combination);

        foreach ($modules as $module) {
            if ($parentModule->getArchiveName() === $module->getArchiveName()) {
                continue;
            }
            $this->internalPullAndInstall($module);
        }
    }

    public function update(Module $module): ?Module
    {
        $installedModule = $module->getInstalledVersion();
        $newModule = $module->getNewestVersion();

        $combinationSatisfyerResult = $this->dependencyManager->canBeInstalled($module);

        if (!$combinationSatisfyerResult->foundCombination) {
            $message =
                "Can not update module {$module->getArchiveName()} {$module->getVersion()}. "
                . "No possible combination of versions found";
            StaticLogger::log(LogLevel::WARNING, $message);
            // NOTE: Vielleicht neue class ModuleException hinzufügen
            throw new RuntimeException($message);
        }

        if ($installedModule) {
            $this->uninstall($installedModule);
        }

        $this->pull($newModule);
        $newModule = $this->reload($newModule);

        $this->internalInstall($newModule);
        $this->createAutoloadFile();

        return $newModule;
    }

    public function updateWithDependencies(Module $module): ?Module
    {
        $installedModule = $module->getInstalledVersion();
        $newModule = $module->getNewestVersion();

        $combinationSatisfyerResult = $this->dependencyManager->canBeInstalled($module);

        if (!$combinationSatisfyerResult->foundCombination) {
            $message =
                "Can not update module {$module->getArchiveName()} {$module->getVersion()} with dependencies. "
                . "No possible combination of versions found";
            StaticLogger::log(LogLevel::WARNING, $message);
            // NOTE: Vielleicht neue class ModuleException hinzufügen
            throw new RuntimeException($message);
        }

        if ($installedModule) {
            $this->uninstall($installedModule);
        }

        $this->pull($newModule);
        $newModule = $this->reload($newModule);

        $this->internalInstall($newModule);
        $this->internalInstallDependencies($newModule, $combinationSatisfyerResult->foundCombination);
        $this->createAutoloadFile();

        return $newModule;
    }

    private function reload(Module $module): Module
    {
        $this->localModuleLoader->resetCache();
        $reloadedModule = $this->localModuleLoader->loadByArchiveNameAndVersion(
            $module->getArchiveName(),
            $module->getVersion()
        );

        if (!$reloadedModule) {
            $message = "Can not reload module {$module->getArchiveName()} {$module->getVersion()}";
            StaticLogger::log(LogLevel::WARNING, $message);
            // NOTE: Vielleicht neue class ModuleException hinzufügen
            throw new RuntimeException($message);
        }

        return $reloadedModule;
    }

    public function revertChanges(Module $module): void
    {
        if (!$module->isInstalled()) {
            $message =
                "Can not revert changes because {$module->getArchiveName()} {$module->getVersion()} is not installed.";
            StaticLogger::log(LogLevel::WARNING, $message);
            // NOTE: Vielleicht neue class ModuleException hinzufügen
            throw new RuntimeException($message);
        }

        $this->internalInstall($module);
    }

    private function createAutoloadFile(): void
    {
        $this->localModuleLoader->resetCache();
        $localModules = $this->localModuleLoader->loadAllVersions();
        $installedLocalModules = $this->moduleFilter->filterInstalled($localModules);

        $namespaceEntrys = [];
        foreach ($installedLocalModules as $module) {
            $autoload = $module->getAutoload();

            if (!$autoload) {
                continue;
            }

            if (!$autoload['psr-4']) {
                continue;
            }

            foreach ($autoload['psr-4'] as $namespace => $path) {
                $path = str_replace($module->getSourceMmlcDir(), 'vendor-mmlc/' . $module->getArchiveName(), $path);
                $namespaceEntrys[] =
                    '$loader->setPsr4(\'' . $namespace . '\\\', DIR_FS_DOCUMENT_ROOT . \'' . $path . '\');';
            }
        }

        $namespaceEntrys = array_unique($namespaceEntrys);
        $namespaceMapping = implode("\n", $namespaceEntrys);

        $template = \file_get_contents(App::getTemplatesRoot() . '/autoload.php.tmpl');
        $template = \str_replace('{VENDOR_PSR4_NAMESPACE_MAPPINGS}', $namespaceMapping, $template);

        if (!file_exists(App::getShopRoot() . '/vendor-no-composer')) {
            mkdir(App::getShopRoot() . '/vendor-no-composer');
        }
        \file_put_contents(App::getShopRoot() . '/vendor-no-composer/autoload.php', $template);

        if (!file_exists(App::getShopRoot() . '/vendor-mmlc')) {
            mkdir(App::getShopRoot() . '/vendor-mmlc');
        }
        \file_put_contents(App::getShopRoot() . '/vendor-mmlc/autoload.php', $template);
    }

    public function uninstall(Module $module, bool $force = false): bool
    {
        $installedModule = $module->getInstalledVersion();
        if (!$installedModule) {
            return false;
        }

        if ($installedModule->isChanged() && $force === false) {
            $message =
                "Can not uninstall module {$installedModule->getArchiveName()} {$installedModule->getVersion()} "
                . "because the module has changes.";
            StaticLogger::log(LogLevel::WARNING, $message);
            // NOTE: Vielleicht neue class ModuleException hinzufügen
            throw new RuntimeException($message);
        }

        $this->internalUninstall($installedModule);
        $this->createAutoloadFile();

        return true;
    }

    private function internalUninstall(Module $module): void
    {
        $this->uninstallFiles();
        $this->removeModuleHashFile();
    }

    private function uninstallFiles(Module $module): void
    {
        // Uninstall from shop-root
        $files = $module->getSrcFilePaths();
        foreach ($files as $file) {
            $file = ModulePathMapper::moduleSrcToShopRoot($file);
            $dest = App::getShopRoot() . $file;
            $this->uninstallFile($dest);
        }

        // Uninstall from shop-vendor-mmlc
        $files = $module->getSrcMmlcFilePaths();
        foreach ($files as $file) {
            $file = ModulePathMapper::moduleSrcMmlcToShopVendorMmlc($file, $module->getArchiveName());
            $dest = App::getShopRoot() . $file;
            $this->uninstallFile($dest);
            FileHelper::deletePathIsEmpty($dest);
        }
    }

    private function removeModuleHashFile(Module $module): void
    {
        if (file_exists($module->getHashPath())) {
            unlink($module->getHashPath());
        }
    }


    private function installFile(string $src, string $dest, bool $overwrite = false): bool
    {
        if (!file_exists($src)) {
            return false;
        }

        if ($overwrite == false && (file_exists($dest) || is_link($dest))) {
            return false;
        } elseif ($overwrite == true && (file_exists($dest) || is_link($dest))) {
            unlink($dest);
        }

        FileHelper::makeDirIfNotExists($dest);

        if (file_exists($dest) || is_link($dest)) {
            return false;
        }

        if (Config::getInstallMode() == 'link') {
            symlink($src, $dest);
        } else {
            copy($src, $dest);
        }

        return true;
    }

    private function uninstallFile(string $dest): void
    {
        if (file_exists($dest)) {
            unlink($dest);
        }
    }
}
