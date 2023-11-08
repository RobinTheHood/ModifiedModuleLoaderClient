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

class UnsaveModuleInstaller
{
    /** @var DependencyManager */
    private $dependencyManager;

    /** @var LocalModuleLoader */
    private $localModuleLoader;

    /** @var ArchivePuller */
    private $archivePuller;

    /** @var ArchiveHandler */
    private $archiveHandler;

    public static function create(int $mode): UnsaveModuleInstaller
    {
        $dependencyManager = DependencyManager::create($mode);
        $localModuleLoader = LocalModuleLoader::create($mode);
        $archivePuller = ArchivePuller::create();
        $archiveHandler = ArchiveHandler::create($mode);
        $moduleInstaller = new UnsaveModuleInstaller(
            $dependencyManager,
            $localModuleLoader,
            $archivePuller,
            $archiveHandler
        );
        return $moduleInstaller;
    }

    public static function createFromConfig(): UnsaveModuleInstaller
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

    // Interne Methoden überprüfen nicht, ob ein Befehl ausgeführt werden kann, sie tuen bzw. versuchen es einfachen.
    public function pull(Module $module): Module
    {
        $archiveUrl = $this->getArchiveUrl($module);
        $archive = $this->archivePuller->pull($module->getArchiveName(), $module->getVersion(), $archiveUrl);
        $this->archiveHandler->extract($archive);
        $pulledModule = $this->reload($module);
        return $pulledModule;
    }

    public function delete(Module $module): void
    {
        $this->deleteModuleFiles($module);
    }

    public function install(Module $module): void
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
        $this->installWithoutDependencies($module);
        // Modul Abhängigkeiten installieren
        $this->installDependencies($module, $combinationSatisfyerResult->foundCombination);
    }

    public function installWithoutDependencies(Module $module): void
    {
        $moduleFileInstaller = new ModuleFileInstaller();
        $moduleFileInstaller->install($module);
        $this->reload($module);
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
    private function pullAndInstallWithoutDependencies(Module $module): void
    {
        if ($module->isInstalled()) {
            return;
        }

        if ($module->isLoaded()) {
            $pulledModule = $module;
        } else {
            $pulledModule = $this->pull($module);
        }

        $this->installWithoutDependencies($pulledModule);
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
    public function installDependencies(Module $parentModule, Combination $combination): void
    {
        $modules = $this->dependencyManager->getAllModulesFromCombination($combination);

        foreach ($modules as $module) {
            if ($parentModule->getArchiveName() === $module->getArchiveName()) {
                continue;
            }
            $this->pullAndInstallWithoutDependencies($module);
        }
    }

    public function update(Module $installedModule): Module
    {
        $newModule = $installedModule->getNewestVersion();

        $installedModuleText = "module {$installedModule->getArchiveName()} {$installedModule->getVersion()}";
        $newModuleText = "module {$newModule->getArchiveName()} {$newModule->getVersion()}";

        $combinationSatisfyerResult = $this->dependencyManager->canBeInstalled($newModule);

        if (!$combinationSatisfyerResult->foundCombination) {
            $message = "Can not update $installedModuleText to $newModuleText. No possible combination of versions found";
            StaticLogger::log(LogLevel::WARNING, $message);
            // NOTE: Vielleicht neue class ModuleException hinzufügen
            throw new RuntimeException($message);
        }

        if ($newModule->isLoaded()) {
            $loadedNewModule = $newModule;
        } else {
            $loadedNewModule = $this->pull($newModule);
        }

        $this->uninstall($installedModule);
        // Modul installieren
        $this->installWithoutDependencies($loadedNewModule);
        // Modul Abhängigkeiten installieren
        $this->installDependencies($loadedNewModule, $combinationSatisfyerResult->foundCombination);

        return $loadedNewModule;
    }

    public function updateWithoutMissingDependencies(Module $installedModule, bool $skipDependencyCheck = false): Module
    {
        $newModule = $installedModule->getNewestVersion();

        $installedModuleText = "module {$installedModule->getArchiveName()} {$installedModule->getVersion()}";
        $newModuleText = "module {$newModule->getArchiveName()} {$newModule->getVersion()}";

        if (!$skipDependencyCheck) {
            // Wirft eine Exception, wenn keine passenen Kombination gefunden wurde.
            $combinationSatisfyerResult = $this->dependencyManager->canBeInstalled($newModule);

            if (!$combinationSatisfyerResult->foundCombination) {
                $message = "Can not update $installedModuleText to $newModuleText. No possible combination of versions found";
                StaticLogger::log(LogLevel::WARNING, $message);
                // NOTE: Vielleicht neue class ModuleException hinzufügen
                throw new RuntimeException($message);
            }
        }

        if ($newModule->isLoaded()) {
            $loadedNewModule = $newModule;
        } else {
            $loadedNewModule = $this->pull($newModule);
        }

        $this->uninstall($installedModule);
        $this->installWithoutDependencies($loadedNewModule);

        return $loadedNewModule;
    }

    public function uninstall(Module $module, bool $force = false): void
    {
        $moduleText = "module {$module->getArchiveName()}";

        $installedModule = $module->getInstalledVersion();
        if (!$installedModule) {
            $message = "Can not uninstall $moduleText because module is not installed";
            StaticLogger::log(LogLevel::WARNING, $message);
            // NOTE: Vielleicht neue class ModuleException hinzufügen
            throw new RuntimeException($message);
        }

        $installedModuleText = "module {$installedModule->getArchiveName()} {$installedModule->getVersion()}";

        if ($installedModule->isChanged() && $force === false) {
            $message = "Can not uninstall $installedModuleText because the module has changes.";
            StaticLogger::log(LogLevel::WARNING, $message);
            // NOTE: Vielleicht neue class ModuleException hinzufügen
            throw new RuntimeException($message);
        }

        $moduleFileInstaller = new ModuleFileInstaller();
        $moduleFileInstaller->uninstall($installedModule);

        $this->reload($installedModule);
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
