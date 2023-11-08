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
    public function pull(Module $module): Module
    {
        $moduleText = "module {$module->getArchiveName()} {$module->getVersion()}";

        if ($module->isLoaded()) {
            throw new Exception("Can not pull $moduleText. Modul is already loaded.");
        }

        return $this->internalPull($module);
    }

    // Interne Methoden überprüfen nicht, ob ein Befehl ausgeführt werden kann, sie tuen bzw. versuchen es einfachen.
    private function internalPull(Module $module): Module
    {
        $archiveUrl = $this->getArchiveUrl($module);
        $archive = $this->archivePuller->pull($module->getArchiveName(), $module->getVersion(), $archiveUrl);
        $this->archiveHandler->extract($archive);
        $pulledModule = $this->reload($module);
        return $pulledModule;
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
        $moduleText = "module {$module->getArchiveName()} {$module->getVersion()}";

        if (!$module->isLoaded()) {
            throw new Exception("Can not delete $moduleText. Module is not loaded.");
        }

        if ($module->isInstalled()) {
            throw new Exception("Can not delete $moduleText. Module is installed.");
        }

        $this->internalDelete($module);
    }

    private function internalDelete(Module $module): void
    {
        $this->deleteModuleFiles($module);
    }

    /**
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
    public function install(Module $module): void
    {
        $moduleText = "module {$module->getArchiveName()} {$module->getVersion()}";

        if ($module->isInstalled()) {
            throw new Exception("Can not install $moduleText. Module is already installed.");
        }

        $this->internalInstall($module);

        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();
    }

    private function internalInstall(Module $module): void
    {
        $moduleText = "module {$module->getArchiveName()} {$module->getVersion()}";

        $combinationSatisfyerResult = $this->dependencyManager->canBeInstalled($module, ['']);

        if (!$combinationSatisfyerResult->foundCombination) {
            $message =
                "Can not install $moduleText with dependencies. No possible combination of versions found";
            StaticLogger::log(LogLevel::WARNING, $message);
            // NOTE: Vielleicht neue class ModuleInstallerException hinzufügen
            throw new RuntimeException($message);
        }

        // Modul installieren
        $this->internalInstallWithoutDependencies($module);
        // Modul Abhängigkeiten installieren
        $this->internalInstallDependencies($module, $combinationSatisfyerResult->foundCombination);
    }

    /**
     * //TODO: Umbennen in installWithoutDependencies()
     *
     * Installiert ein Modul (archiveName, Version) in das Shop System ABER lädt und installiert KEINE Abhängigkeiten /
     * abhängige Module nach. Sind nicht alle Abhängigkeiten erfüllt, wird nicht installiert und eine Exception
     * geworfen.
     *
     * @param bool $skipDependencyCheck skip dependency check.
     *
     * Mögliche Fehler und Exceptions
     * - [ ] alle Fehler aus internalInstall()
     * - [x] Nicht alle Abhängigkeiten sind vorhanden
     * - [ ] Keine Schreibrechte im Shop
     * - [ ] Nicht alle Datein konnten im Shop installiert werden.
     * - [ ] Autoload kann nicht aktuallisiert werden
     * - [x] Das Modull ist bereits installiert
     */
    public function installWithoutDependencies(Module $module, bool $skipDependencyCheck = false): void
    {
        if ($module->isInstalled()) {
            throw new Exception("Can not install module {$module->getArchiveName()}. Module is already installed.");
        }

        if (!$skipDependencyCheck) {
            // Wirft eine DependencyException, wenn dass Modul nicht installiert werden kann.
            $this->dependencyManager->canBeInstalled($module);
        }

        $this->internalInstallWithoutDependencies($module);

        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();
    }

    private function internalInstallWithoutDependencies(Module $module): void
    {
        $moduleFileInstaller = new ModuleFileInstaller();
        $moduleFileInstaller->install($module);
        $this->reload($module);
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





    /**
     * Retrieves the archive URL for a given module.
     *
     * This method is responsible for obtaining the archive URL for a specific module by making an API request. It
     * constructs the URL to download the module's archive, handles potential errors in the API response, and returns
     * the archive URL.
     *
     * @param Module $module The Module for which the archive URL should be retrieved.
     *
     * @return string The URL to download the module's archive.
     *
     * @throws Exception
     *      If the API response is empty or lacks the necessary information to construct the archive URL, an Exception
     *      is thrown with a detailed error message.
     */
    private function getArchiveUrl(Module $module): string
    {
        $moduleText = "module {$module->getArchiveName()} {$module->getVersion()}";

        $apiRequest = new ApiRequest();
        $result = $apiRequest->getArchive($module->getArchiveName(), $module->getVersion());

        $content = $result['content'] ?? [];
        if (!$content) {
            throw new Exception("Can not pull $moduleText. ApiRespond is empty.");
        }

        $archiveUrl = $content['archiveUrl'] ?? '';
        if (!$archiveUrl) {
            throw new Exception("Can not pull $moduleText. archiveUrl is empty.");
        }

        return $archiveUrl;
    }

    /**
     * Loads and installs a module.
     *
     * This method is responsible for loading and installing a module specified by the provided Module object. It checks
     * whether the module is already installed, and if not, it attempts to load and install it. The method also handles
     * potential errors during the loading and installation process.
     *
     * @param Module $module The Module to be loaded and installed.
     *
     * @throws RuntimeException
     *      If the module cannot be loaded or installed successfully, a RuntimeException is thrown with a detailed
     *      error message.
     */
    private function internalPullAndInstall(Module $module): void
    {
        if ($module->isInstalled()) {
            return;
        }

        if ($module->isLoaded()) {
            $pulledModule = $module;
        } else {
            $pulledModule = $this->internalPull($module);
        }

        $this->internalInstallWithoutDependencies($pulledModule);
    }

    /**
     * Installs the dependencies specified in the given Combination for a parent Module.
     *
     * This method is used internally to install the dependencies specified in a provided Combination for a parent
     * Module. It retrieves the required modules from the Combination and iterates through them, checking for
     * compatibility and installing each one. The parent Module is excluded from the installation process.
     *
     * @param Module $parentModule The parent Module for which dependencies need to be installed.
     * @param Combination $combination The Combination specifying the dependencies to be installed.
     *
     * @throws DependencyException
     *      If any of the dependencies cannot be installed due to conflicting versions or other issues,
     *      a DependencyException may be thrown with details.
     */
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
        $moduleText = "module {$module->getArchiveName()} {$module->getVersion()}";

        $this->localModuleLoader->resetCache();
        $reloadedModule = $this->localModuleLoader->loadByArchiveNameAndVersion(
            $module->getArchiveName(),
            $module->getVersion()
        );

        if (!$reloadedModule) {
            $message = "Can not reload $moduleText.";
            StaticLogger::log(LogLevel::WARNING, $message);
            // NOTE: Vielleicht neue class ModuleException hinzufügen
            throw new RuntimeException($message);
        }

        return $reloadedModule;
    }
}
