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

use RuntimeException;
use RobinTheHood\ModifiedModuleLoaderClient\Config;
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\Api\V1\ApiRequest;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Archive\ArchivePuller;
use RobinTheHood\ModifiedModuleLoaderClient\Archive\ArchiveHandler;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\Combination;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\DependencyManager;

class ModuleInstaller
{
    /** @var DependencyManager */
    private $dependencyManager;

    /** @var LocalModuleLoader */
    private $localModuleLoader;

    /** @var ArchivePuller */
    private $archivePuller;

    /** @var ArchiveHandler */
    private $archiveHandler;

    /** @var ModuleFileInstaller */
    private $moduleFileInstaller;

    public static function create(int $mode): ModuleInstaller
    {
        $dependencyManager = DependencyManager::create($mode);
        $localModuleLoader = LocalModuleLoader::create($mode);
        $archivePuller = ArchivePuller::create();
        $archiveHandler = ArchiveHandler::create($mode);
        $moduleFileInstaller = ModuleFileInstaller::create();
        $moduleInstaller = new ModuleInstaller(
            $dependencyManager,
            $localModuleLoader,
            $archivePuller,
            $archiveHandler,
            $moduleFileInstaller
        );
        return $moduleInstaller;
    }

    public static function createFromConfig(): ModuleInstaller
    {
        return self::create(Config::getDependenyMode());
    }

    public function __construct(
        DependencyManager $dependencyManager,
        LocalModuleLoader $localModuleLoader,
        ArchivePuller $archivePuller,
        ArchiveHandler $archiveHandler,
        ModuleFileInstaller $moduleFileInstaller
    ) {
        $this->dependencyManager = $dependencyManager;
        $this->localModuleLoader = $localModuleLoader;
        $this->archivePuller = $archivePuller;
        $this->archiveHandler = $archiveHandler;
        $this->moduleFileInstaller = $moduleFileInstaller;
    }

    /**
     * Downloads and prepares a module for installation.
     *
     * This method is responsible for downloading a module's archive, extracting its contents, and preparing it for
     * installation. It performs several checks to ensure the module is not already loaded and retrieves the archive URL
     * using the specified Module object. Upon successful execution, the method returns the Module instance representing
     * the downloaded and prepared module.
     *
     * @param Module $module The Module to be pulled and prepared for installation.
     *
     * @return Module The Module instance representing the downloaded and prepared module.
     *
     * @throws RuntimeException
     *      If the module is already loaded or if any errors occur during the download, extraction, or preparation
     *      process, a RuntimeException is thrown with a detailed error message.
     */
    public function pull(Module $module): Module
    {
        $moduleText = "module {$module->getArchiveName()} {$module->getVersion()}";

        if ($module->isLoaded()) {
            $this->error("Can not pull $moduleText. Modul is already loaded.");
        }

        // Retrieve the module's archive URL
        $archiveUrl = $this->getArchiveUrl($module);

        // Download and extract the module's archive
        $archive = $this->archivePuller->pull($module->getArchiveName(), $module->getVersion(), $archiveUrl);
        $this->archiveHandler->extract($archive);

        // Reload and return the Module instance
        $pulledModule = $this->reload($module);
        return $pulledModule;
    }

    /**
     * Deletes a loaded module's files.
     *
     * This method is responsible for deleting the files associated with a loaded module. It checks whether the module
     * is loaded and, if required, verifies whether it's uninstalled before proceeding with the deletion. If the 'force'
     * parameter is set to true, the method will skip the installation check and forcefully delete the module. It then
     * calls the 'deleteModuleFiles' method to remove the module's files from the system.
     *
     * @param Module $module The loaded module to be deleted.
     * @param bool $force Set to true to force the deletion even if the module is installed.
     *
     * @throws RuntimeException
     *      If the module is not loaded or is installed (and 'force' is false), a RuntimeException is thrown with a
     *      detailed error message. Any errors encountered during the deletion process are also reported via exceptions.
     */
    public function delete(Module $module, bool $force = false): void
    {
        $moduleText = "module {$module->getArchiveName()} {$module->getVersion()}";

        if (!$module->isLoaded()) {
            $this->error("Can not delete $moduleText. Module is not loaded.");
        }

        if (!$force && $module->isInstalled()) {
            $this->error("Can not delete $moduleText. Module is installed.");
        }

        $this->deleteModuleFiles($module);
    }

    /**
     * Installs a module and its dependencies.
     *
     * This method is responsible for installing a module along with its dependencies into the shop system. It checks
     * whether the module is already installed (unless 'force' is set to true) and ensures that a valid combination of
     * versions for the module's dependencies can be found. If the installation is successful, it proceeds to install
     * its dependencies. The method offers the flexibility to force the installation of the module even if it's already
     * installed and skip the dependency check. Any errors encountered during the installation process are reported via
     * exceptions.
     *
     * @param Module $module The module to be installed.
     * @param bool $force Set to true to force the installation even if the module is already installed.
     *
     * @throws RuntimeException
     *      If the module is already installed (and 'force' is false) or if no valid combination of versions for the
     *      dependencies can be found, a RuntimeException is thrown with detailed error messages. Any other errors
     *      during the installation process are also reported via exceptions.
     */
    public function install(Module $module, bool $force = false): void
    {
        $moduleText = "module {$module->getArchiveName()} {$module->getVersion()}";

        $installedModule = $module->getInstalledVersion();
        if (!$force && $installedModule) {
            $installedModuleText = "module {$installedModule->getArchiveName()} {$installedModule->getVersion()}";
            $this->error("Can not install $moduleText, because $installedModuleText is already installed.");
        }

        $combinationSatisfyerResult = $this->dependencyManager->canBeInstalled($module);

        if (!$combinationSatisfyerResult->foundCombination) {
            $this->error("Can not install $moduleText with dependencies. No possible combination of versions found");
        }

        $this->installWithoutDependencies($module, true, true);
        $this->installDependencies($module, $combinationSatisfyerResult->foundCombination);
    }

    /**
     * Install a module without installing its dependencies.
     *
     * This method is responsible for installing a module into the shop system without installing its dependencies. It
     * checks whether the module is already installed (unless 'force' is set to true) and optionally performs a
     * dependency check to ensure that a valid combination of versions for the module can be found (unless
     * 'skipDependencyCheck' is set to true). Any errors during the installation process are reported via exceptions.
     *
     * @param Module $module
     *      The module to be installed.
     * @param bool $skipDependencyCheck
     *      Set to true to skip the dependency check (default is false).
     * @param bool $force
     *      Set to true to force the installation even if the module is already installed (default is false).
     */
    public function installWithoutDependencies(
        Module $module,
        bool $skipDependencyCheck = false,
        bool $force = false
    ): void {
        $moduleText = "module {$module->getArchiveName()} {$module->getVersion()}";

        if (!$force && $module->isInstalled()) {
            $this->error("Can not install $moduleText. Module is already installed.");
        }

        if (!$skipDependencyCheck) {
            $combinationSatisfyerResult = $this->dependencyManager->canBeInstalled($module);

            if (!$combinationSatisfyerResult->foundCombination) {
                $this->error("Can not update $moduleText. No possible combination of versions found");
            }
        }

        $this->moduleFileInstaller->install($module);
        $this->reload($module);
    }

    /**
     * Update a module to its newest version while potentially updating its dependencies.
     *
     * This method is responsible for updating a module to its newest version. It verifies whether the module is
     * installed (unless 'force' is set to true) and ensures that a valid combination of versions for the module's
     * dependencies can be found. The newest version is pulled and installed, and its dependencies are also installed as
     * necessary. Any errors during the update process are reported via exceptions, and the loaded new module is
     * returned.
     *
     * @param Module $module The module to be updated.
     * @param bool $force Set to true to force the update even if the module is not installed (default is false).
     */
    public function update(Module $installedModule, Module $newModule, bool $force = false): void
    {
        $moduleText = "module {$installedModule->getArchiveName()} {$installedModule->getVersion()}";

        if (!$force && !$installedModule->isInstalled()) {
            $this->error("Can not update $moduleText. Module is not installed.");
        }

        $moduleText = "module {$installedModule->getArchiveName()} {$installedModule->getVersion()}";
        $newModuleText = "module {$newModule->getArchiveName()} {$newModule->getVersion()}";

        if ($installedModule->getVersion() === $newModule->getVersion()) {
            $this->error("Can not update $moduleText to $newModuleText.");
        }

        $combinationSatisfyerResult = $this->dependencyManager->canBeInstalled($newModule);
        $foundCombination = $combinationSatisfyerResult->foundCombination;

        if (!$foundCombination) {
            $this->error(
                "Can not update $moduleText to $newModuleText."
                . " No possible combination of versions found"
            );
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
        $this->installDependencies($loadedNewModule, $foundCombination);
    }

    /**
     *
     */
    public function updateWithoutDependencies(
        Module $instaledModule,
        Module $newModule,
        bool $skipDependencyCheck = false,
        bool $force = false
    ): Module {
        $moduleText = "module {$instaledModule->getArchiveName()} {$instaledModule->getVersion()}";

        if (!$force && !$instaledModule->isInstalled()) {
            $this->error("Can not update $moduleText. Module is not installed.");
        }

        $moduleText = "module {$instaledModule->getArchiveName()} {$instaledModule->getVersion()}";
        $newModuleText = "module {$newModule->getArchiveName()} {$newModule->getVersion()}";

        if (!$skipDependencyCheck) {
            $combinationSatisfyerResult = $this->dependencyManager->canBeInstalled($newModule);

            if (!$combinationSatisfyerResult->foundCombination) {
                $this->error(
                    "Can not update $moduleText to $newModuleText."
                    . " No possible combination of versions found"
                );
            }
        }

        if ($newModule->isLoaded()) {
            $loadedNewModule = $newModule;
        } else {
            $loadedNewModule = $this->pull($newModule);
        }

        $this->uninstall($instaledModule);
        $this->installWithoutDependencies($loadedNewModule);

        return $loadedNewModule;
    }

    public function discard(Module $module, bool $withTemplate, bool $force): void
    {
        $moduleText = "module {$module->getArchiveName()} {$module->getVersion()}";

        if (!$force && !$module->isInstalled()) {
            $this->error("Can not revert changes because $moduleText is not installed.");
        }

        $this->moduleFileInstaller->install($module, $withTemplate);
    }

    public function uninstall(Module $module, bool $force = false): void
    {
        $moduleText = "module {$module->getArchiveName()}";

        if (!$module->isInstalled()) {
            $this->error("Can not uninstall $moduleText because module is not installed.");
        }

        $moduleText = "module {$module->getArchiveName()} {$module->getVersion()}";


        if ($module->isChanged() && $force === false) {
            $this->error("Can not uninstall $moduleText because the module has changes.");
        }

        $this->moduleFileInstaller->uninstall($module);

        $this->reload($module);
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
     * @throws \RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\DependencyException
     *      If any of the dependencies cannot be installed due to conflicting versions or other issues,
     *      a DependencyException may be thrown with details.
     */
    private function installDependencies(Module $parentModule, Combination $combination): void
    {
        $modules = $this->dependencyManager->getAllModulesFromCombination($combination);

        foreach ($modules as $module) {
            if ($parentModule->getArchiveName() === $module->getArchiveName()) {
                continue;
            }
            $this->pullAndInstallWithoutDependencies($module);
        }
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
     * @throws \Exception
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
            $this->error("Can not pull $moduleText. ApiRespond is empty.");
        }

        $archiveUrl = $content['archiveUrl'] ?? '';
        if (!$archiveUrl) {
            $this->error("Can not pull $moduleText. archiveUrl is empty.");
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
            $this->error("Can not reload $moduleText.");
        }

        return $reloadedModule;
    }

    /**
     * // NOTE: Vielleicht neue class ModuleInstallerException hinzufügen
     * @return never
     */
    private function error(string $message): void
    {
        // StaticLogger::log(LogLevel::WARNING, $message);
        throw new RuntimeException($message);
    }
}
