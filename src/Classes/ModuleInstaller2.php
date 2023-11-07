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

use Exception;
use RobinTheHood\ModifiedModuleLoaderClient\Api\V1\ApiRequest;
use RobinTheHood\ModifiedModuleLoaderClient\Archive\ArchiveHandler;
use RobinTheHood\ModifiedModuleLoaderClient\Archive\ArchivePuller;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\Combination;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\DependencyManager;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Logger\LogLevel;
use RobinTheHood\ModifiedModuleLoaderClient\Logger\StaticLogger;
use RuntimeException;

class ModuleInstaller2
{
    /** @var DependencyManager */
    private $dependencyManager;

    /** @var LocalModuleLoader */
    private $localModuleLoader;

    /** @var ArchivePuller */
    private $archivePuller;

    /** @var ArchiveHandler */
    private $archiveHandler;

    public static function create(int $mode): ModuleInstaller2
    {
        $dependencyManager = DependencyManager::create($mode);
        $localModuleLoader = LocalModuleLoader::create($mode);
        $archivePuller = ArchivePuller::create();
        $archiveHandler = ArchiveHandler::create($mode);
        $moduleInstaller = new ModuleInstaller2(
            $dependencyManager,
            $localModuleLoader,
            $archivePuller,
            $archiveHandler
        );
        return $moduleInstaller;
    }

    public static function createFromConfig(): ModuleInstaller2
    {
        return self::create(Config::getDependenyMode());
    }

    public function __construct(
        DependencyManager $dependencyManager,
        LocalModuleLoader $localModuleLoader,
        ArchivePuller $archivePuller,
        ArchiveHandler $archiveHandler
    ) {
        $this->dependencyManager = $dependencyManager;
        $this->localModuleLoader = $localModuleLoader;
        $this->archivePuller = $archivePuller;
        $this->archiveHandler = $archiveHandler;
    }

    /**
     * Lädt ein Modul (archiveName, Version) vom Server herunter.
     *
     * Mögliche Fehler und Exceptions
     * - [ ] Keine Verbindung zum Server - NoServerConnectionException extends PullException
     * - [ ] Nicht genügent Speicherplatz - InsufficientDiskSpaceException extends PullException
     * - [ ] Zip Datei kann nicht entpackt werden - UnzipFailedException extends PullException
     * - [ ] Keine Schreibrechte für Archives - NoWritePermissionForArchivesException extends PullException
     * - [ ] Keine Schreibrechte für Modules - NoWritePermissionForModulesException extends PullException
     * - [x] Das Modul ist bereits geladen - ModuleAlreadyLoadedException extends PullException
     */
    public function pull(Module $module)
    {
        if ($module->isLoaded()) {
            throw new Exception("Can not pull module {$module->getArchiveName()}. Modul is already loaded.");
        }

        $archiveUrl = $this->getArchiveUrl($module);

        try {
            $archive = $this->archivePuller->pull($module->getArchiveName(), $module->getVersion(), $archiveUrl);
            $this->archiveHandler->extract($archive);
            return true;
        } catch (RuntimeException $e) {
            //Can not pull Archive
            return false;
        }
    }

    /**
     * Löscht ein Modul (archiveName, Version) das bereits heruntergeladen wurde.
     *
     * Mögliche Fehler und Exceptions
     * - [ ] Es ist ein reines lokael Modul und kann deswegen nicht gelöscht werden
     * - [x] Das Modul ist nicht geladen und kann deswegen nicht gelöscht werden
     * - [x] Das Modul ist noch installiert und kann deswegen nicht gelöscht werden
     * - [ ] Keine Schreibrechte für Modules
     *
     * Fragen
     * - Was passiert, wenn nicht alle Datein gelöscht werden konnten, dann ist das Modul in einem defekten Zustand
     */
    public function delete(Module $module): void
    {
        if (!$module->isLoaded()) {
            throw new Exception("Can not delete module {$module->getArchiveName()}. Module is not loaded.");
        }

        if ($module->isInstalled()) {
            throw new Exception("Can not delete module {$module->getArchiveName()}. Module is installed.");
        }

        $this->deleteModuleFiles($module);
    }

    /**
     * //TODO: Umbenennen in install()
     *
     * Installiert ein Modul (archiveName, Version) in das Shop System UND lädt und installiert alle Abhängigkeiten /
     * abhängige Module nach.
     *
     * Mögliche Fehler und Exceptions
     * - alle Fehler aus internalInstall()
     * - alle Fehler aus internalInstallDependencies()
     * - Keine passende kombination an Abhänigkeiten gefunden
     * - Autoload kann nicht aktuallisiert werden
     * - Das Modull ist bereits installiert
     */
    public function installWithtDependencies(Module $module): void
    {
        if ($module->isInstalled()) {
            throw new Exception(
                "Can not install module {$module->getArchiveName()} {$module->getVersion()}. "
                . "Module is already installed."
            );
        }

        $combinationSatisfyerResult = $this->dependencyManager->canBeInstalled($module, ['']);

        if (!$combinationSatisfyerResult->foundCombination) {
            $message =
                "Can not install module {$module->getArchiveName()} {$module->getVersion()} with dependencies. "
                . "No possible combination of versions found";
            StaticLogger::log(LogLevel::WARNING, $message);
            // NOTE: Vielleicht neue class ModuleInstallerException hinzufügen
            throw new RuntimeException($message);
        }

        $this->uninstall($module);

        $moduleFileInstaller = new ModuleFileInstaller();
        $moduleFileInstaller->install($module);

        $this->internalInstallDependencies($module, $combinationSatisfyerResult->foundCombination);

        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();
    }

    /**
     * //TODO: Umbennen in installWithoutDependencies()
     *
     * Installiert ein Modul (archiveName, Version) in das Shop System ABER lädt und installiert KEINE Abhängigkeiten /
     * abhängige Module nach. Sind nicht alle Abhängigkeiten erfüllt, wird nicht installiert und eine Exception
     * geworfen.
     *
     * @param bool $force skip dependency check.
     *
     * Mögliche Fehler und Exceptions
     * - [ ] alle Fehler aus internalInstall()
     * - [x] Nicht alle Abhängigkeiten sind vorhanden
     * - [ ] Keine Schreibrechte im Shop
     * - [ ] Nicht alle Datein konnten im Shop installiert werden.
     * - [ ] Autoload kann nicht aktuallisiert werden
     * - [x] Das Modull ist bereits installiert
     */
    public function install(Module $module, bool $force = false): void
    {
        if ($module->isInstalled()) {
            throw new Exception("Can not install module {$module->getArchiveName()}. Module is already installed.");
        }

        if (!$force) {
            // Wirft eine DependencyException, wenn dass Modul nicht installiert werden kann.
            $this->dependencyManager->canBeInstalled($module);
        }

        $moduleFileInstaller = new ModuleFileInstaller();
        // Wirft eine Exception, wenn es nicht installiert werden konnte.
        $moduleFileInstaller->install($module);

        $autoloadFileCreator = new AutoloadFileCreator();
        // TODO: In createAutoloadFile() Exceptions werfen im Fehlerfall
        $autoloadFileCreator->createAutoloadFile();
    }

    /**
     * //TODO: Nicht zur Neusten sondern zu höchst möglichsten Version aktualisieren.
     * //TODU: Umbennen in updateWithoutInstallDependencies()
     *
     * Aktualiseirt NUR das Modul (vendorName) auf die neuste Version. Es werden keine fehlenden Abhänggigkeiten
     * installiert. Es werden keine Abhänggigkeiten aktualisiert. Können nicht alle Abhängigkeiten erfüllt werten,
     * wird nicht aktualisiert und eine Exception geworfen.
     */
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

        $moduleFileInstaller = new ModuleFileInstaller();
        $moduleFileInstaller->install($newModule);

        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();

        return $newModule;
    }

    /**
     * //TODO: Nicht zur Neusten sondern zu höchst möglichsten Version aktualisieren.
     * //TODO: Umbennen in update()
     *
     * Aktuallisiert das Modul (vendorName) auf die neuste Version. Dabei werden keine Abhänggigkeiten
     * aktualisiert. Kommen durch das Update jedoch neue Abhänigkeiten hinzu, werden diese installt. Können nicht alle
     * Abhängigkeiten erfüllt werten, wird nicht aktualisiert und eine Exception geworfen.
     */
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

        $moduleFileInstaller = new ModuleFileInstaller();
        $moduleFileInstaller->install($newModule);
        $this->internalInstallDependencies($newModule, $combinationSatisfyerResult->foundCombination);

        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();

        return $newModule;
    }

    /**
     * //TODO: Umbennen in discard
     *
     * Entfernt alle Änderungen die an den Modul-Dateien im Shop gemacht wurden. Änderungen an Template Dateien werden
     * nicht rückgängig gemacht.
     *
     * //TODO: bool $force Parameter einführen der auch Änderungen am Template entfernt.
     */
    public function revertChanges(Module $module): void
    {
        if (!$module->isInstalled()) {
            $message =
                "Can not revert changes because {$module->getArchiveName()} {$module->getVersion()} is not installed.";
            StaticLogger::log(LogLevel::WARNING, $message);
            // NOTE: Vielleicht neue class ModuleException hinzufügen
            throw new RuntimeException($message);
        }

        $moduleFileInstaller = new ModuleFileInstaller();
        $moduleFileInstaller->install($module);
    }

    /**
     * Deinstalliert nur das Modul (versionName, Version), wenn es installiert und nicht mehr als abhänigkeit von einem
     * anderen Modul benötigt wird. Es werden keine Abhängigkeiten deinstalliert.
     *
     * Mit der force Option, kann der Abhängigkeits check übersprungen werden und das Modul wird trozdem deinstalliert.
     * Das kann aber zur folge haben, dass andere Module nicht mehr funktionieren.
     */
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

        $moduleFileInstaller = new ModuleFileInstaller();
        $moduleFileInstaller->uninstall($installedModule);

        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();

        return true;
    }






    private function getArchiveUrl(Module $module): string
    {
        $apiRequest = new ApiRequest();
        $result = $apiRequest->getArchive($module->getArchiveName(), $module->getVersion());

        $content = $result['content'] ?? [];
        if (!$content) {
            throw new Exception("Can not pull module {$module->getArchiveName()}. archiveUrl is empty");
        }

        $archiveUrl = $content['archiveUrl'] ?? '';
        if (!$archiveUrl) {
            throw new Exception("Can not pull module {$module->getArchiveName()}. archiveUrl is empty");
        }

        return $archiveUrl;
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

        $moduleFileInstaller = new ModuleFileInstaller();
        $moduleFileInstaller->install($module);
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

    /**
     * Löscht alle Module Dateien aus dem Verzeichnis Modules. Es wird nicht kontrolliert ob das Modul geladen oder
     * installiert ist.
     */
    private function deleteModuleFiles(Module $module): void
    {
        $path = $module->getLocalRootPath() . $module->getModulePath();

        $filePaths = FileHelper::scanDirRecursive($path, FileHelper::FILES_ONLY);

        $dirPaths = FileHelper::scanDirRecursive($path, FileHelper::DIRS_ONLY);
        $dirPaths = array_reverse($dirPaths);
        $dirPaths[] = $path;
        $dirPaths[] = dirname($path);

        // Delete Files
        foreach ($filePaths as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }

        // Delete Folders
        foreach ($dirPaths as $path) {
            if (file_exists($path)) {
                @rmdir($path);
            }
        }
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
}
