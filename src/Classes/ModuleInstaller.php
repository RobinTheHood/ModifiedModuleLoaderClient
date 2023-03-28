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

use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\Archive;
use RobinTheHood\ModifiedModuleLoaderClient\Config;
use RobinTheHood\ModifiedModuleLoaderClient\FileInfo;
use RobinTheHood\ModifiedModuleLoaderClient\Api\V1\ApiRequest;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\Combination;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\DependencyManager;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\ModuleHashFileCreator;
use RuntimeException;

class ModuleInstaller
{
    public function pull(Module $module): bool
    {
        if ($module->isLoaded()) {
            return true;
        }

        $apiRequest = new ApiRequest();
        $result = $apiRequest->getArchive($module->getArchiveName(), $module->getVersion());

        $content = $result['content'] ?? [];
        if (!$content) {
            return false;
        }

        $archiveUrl = $content['archiveUrl'] ?? '';

        if (!$archiveUrl) {
            return false;
        }

        try {
            $archive = Archive::pullArchive($archiveUrl, $module->getArchiveName(), $module->getVersion());
            $archive->untarArchive();
            return true;
        } catch (RuntimeException $e) {
            //Can not pull Archive
            return false;
        }
    }

    public function delete(Module $module)
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

    public function install(Module $module, bool $force = false): void
    {
        if (!$force) {
            $dependencyManager = new DependencyManager();
            $dependencyManager->canBeInstalled($module);
        }

        $this->internalInstall($module);
        $this->createAutoloadFile();
    }

    public function installWithDependencies(Module $module): void
    {
        $dependencyManager = new DependencyManager();
        $combinationSatisfyerResult = $dependencyManager->canBeInstalled($module, ['']);

        if (!$combinationSatisfyerResult->foundCombination) {
            throw new RuntimeException(
                "Can not install module {$module->getArchiveName()} {$module->getVersion()} with dependencies. "
                . "No possible combination of versions found"
            );
        }

        $this->uninstall($module);
        $this->internalInstall($module);
        $this->internalInstallDependencies($module, $combinationSatisfyerResult->foundCombination);
        $this->createAutoloadFile();
    }

    private function internalInstall(Module $module): void
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

        $moduleHashFileCreator = new ModuleHashFileCreator();
        $moduleHashFile = $moduleHashFileCreator->createHashFile($module);
        $moduleHashFile->writeTo($module->getHashPath());
    }

    private function internalPullAndInstall(Module $module): void
    {
        if (!$module->isLoaded()) {
            $this->pull($module);
        }

        $reloaded = $this->reload($module);

        if (!$reloaded->isLoaded()) {
            throw new RuntimeException(
                "Can not pull and install module {$module->getArchiveName()} {$module->getVersion()}. "
                . "Module is not loaded."
            );
        }

        if ($reloaded->isInstalled()) {
            return;
        }

        $this->uninstall($module);
        $this->internalInstall($module);
    }

    private function internalInstallDependencies(Module $parentModule, Combination $combination): void
    {
        $dependencyManager = new DependencyManager();
        $modules = $dependencyManager->getAllModulesFromCombination($combination);

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

        $dependencyManager = new DependencyManager();
        $combinationSatisfyerResult = $dependencyManager->canBeInstalled($module);

        if (!$combinationSatisfyerResult->foundCombination) {
            throw new RuntimeException(
                "Can not update module {$module->getArchiveName()} {$module->getVersion()}. "
                . "No possible combination of versions found"
            );
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

        $dependencyManager = new DependencyManager();
        $combinationSatisfyerResult = $dependencyManager->canBeInstalled($module);

        if (!$combinationSatisfyerResult->foundCombination) {
            throw new RuntimeException(
                "Can not update module {$module->getArchiveName()} {$module->getVersion()} with dependencies. "
                . "No possible combination of versions found"
            );
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
        $moduleLoader = new LocalModuleLoader();
        $moduleLoader->resetCache();
        $reloadedModule = $moduleLoader->loadByArchiveNameAndVersion(
            $module->getArchiveName(),
            $module->getVersion()
        );

        if (!$reloadedModule) {
            throw new RuntimeException("Can not reload module {$module->getArchiveName()} {$module->getVersion()}");
        }

        return $reloadedModule;
    }

    public function revertChanges(Module $module): void
    {
        if (!$module->isInstalled()) {
            throw new RuntimeException(
                "Can not revert changes because {$module->getArchiveName()} {$module->getVersion()} is not installed."
            );
        }

        $this->internalInstall($module);
    }

    private function createAutoloadFile(): void
    {
        $localModuleLoader = LocalModuleLoader::getModuleLoader();
        $localModuleLoader->resetCache();
        $localModules = $localModuleLoader->loadAllVersions();
        $installedLocalModules = ModuleFilter::filterInstalled($localModules);

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

    public function uninstall(Module $module): bool
    {
        $installedModule = $module->getInstalledVersion();
        if (!$installedModule) {
            return false;
        }

        if ($installedModule->isChanged()) {
            throw new RuntimeException(
                "Can not uninstall module {$installedModule->getArchiveName()} {$installedModule->getVersion()} "
                . "because the module has changes."
            );
        }

        $this->internalUninstall($installedModule);
        $this->createAutoloadFile();

        return true;
    }

    private function internalUninstall(Module $module): void
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
