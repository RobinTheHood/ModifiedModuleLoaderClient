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
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager;
use RobinTheHood\ModifiedModuleLoaderClient\Api\V1\ApiRequest;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\ArrayHelper;

class ModuleInstaller
{
    public function pull(Module $module): bool
    {
        if ($module->isLoaded()) {
            return true;
        }

        $apiRequest = new ApiRequest();
        $result = $apiRequest->getArchive($module->getArchiveName(), $module->getVersion());

        if (!ArrayHelper::getIfSet($result, 'content')) {
            return false;
        }

        $archiveUrl = ArrayHelper::getIfSet($result['content'], 'archiveUrl');

        if (!$archiveUrl) {
            return false;
        }

        try {
            $archive = Archive::pullArchive($archiveUrl, $module->getArchiveName(), $module->getVersion());
            $archive->untarArchive();
            return true;
        } catch (\RuntimeException $e) {
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

    public function install(Module $module, $force = false): void
    {
        if (!$force) {
            $dependencyManager = new DependencyManager();
            $dependencyManager->canBeInstalled($module);
        }

        $files = $module->getSrcFilePaths();

        foreach ($files as $file) {
            $src = $module->getLocalRootPath() . $module->getSrcRootPath() . '/' . $file;

            $files = $module->getTemplateFiles($file);
            foreach ($files as $file) {
                $overwrite = false;
                if (!FileInfo::isTemplateFile($file)) {
                    $overwrite = true;
                }

                $file = ModulePathMapper::mmlcToShop($file);

                $dest = App::getShopRoot() . $file;
                $this->installFile($src, $dest, $overwrite);
            }
        }

        $moduleHasher = new ModuleHasher();
        $moduleHasher->hashModule($module);

        $this->createAutoloadFile();
    }

    public function revertChanges(Module $module): void
    {
        if (!$module->isInstalled()) {
            throw new \RuntimeException('Can not revert changes because ' . $module->getArchiveName() . ' is not installed.');
        }

        $this->install($module, true);
    }

    public function installDependencies(Module $module): void
    {
        $dependencyManager = new DependencyManager();
        $modules = $dependencyManager->getAllModules($module);

        foreach ($modules as $depModule) {
            if (!$depModule->isLoaded()) {
                $this->pull($depModule);
            }
        }

        $modules = $dependencyManager->getAllModules($module);
        foreach ($modules as $depModule) {
            $this->uninstall($depModule);
            if ($depModule->isLoaded() && !$depModule->isInstalled()) {
                $this->install($depModule);
            }
        }

        $this->createAutoloadFile();
    }

    public function installWithDependencies(Module $module): void
    {
        $dependencyManager = new DependencyManager();
        $dependencyManager->canBeInstalled($module);

        $this->install($module);
        $this->installDependencies($module);
    }

    public function createAutoloadFile(): void
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
                $namespaceEntrys[] =
                    '$loader->setPsr4(\'' . $namespace . '\\\', DIR_FS_DOCUMENT_ROOT . \'' . $path . '\');';
            }
        }

        $namespaceEntrys = array_unique($namespaceEntrys);
        $namespaceMapping = implode("\n", $namespaceEntrys);

        $template = \file_get_contents(App::getTemplatesRoot() . '/autoload.php.tmpl');
        $template = \str_replace('{VENDOR_PSR4_NAMESPACE_MAPPINGS}', $namespaceMapping, $template);

        @mkdir(App::getShopRoot() . '/vendor-no-composer');
        \file_put_contents(App::getShopRoot() . '/vendor-no-composer/autoload.php', $template);
    }

    //TODO: Better return void type an thorw exception at error
    public function uninstall(?Module $module): bool
    {
        if (!$module) {
            return false;
        }

        $module = $module->getInstalledVersion();
        if (!$module) {
            return false;
        }

        $files = $module->getSrcFilePaths();

        foreach ($files as $file) {
            $file = ModulePathMapper::mmlcToShop($file);
            $dest = App::getShopRoot() . $file;
            $this->uninstallFile($dest);
        }

        $moduleHasher = new ModuleHasher();
        $moduleHasher->unhashModule($module);

        $this->createAutoloadFile();

        return true;
    }

    public function update(Module $module): ?Module
    {
        $oldModule = $module->getInstalledVersion();
        $newModule = $module->getNewestVersion();

        $dependencyManager = new DependencyManager();
        $dependencyManager->canBeInstalled($newModule);

        $this->uninstall($oldModule);
        $this->pull($newModule);

        // Da nach $newModule->pull() das Modul noch nicht lokal inistailisiert
        // sein kann, wird es noch einmal geladen.
        $moduleLoader = new LocalModuleLoader();
        $newModule = $moduleLoader->loadByArchiveNameAndVersion($newModule->getArchiveName(), $newModule->getVersion());

        if (!$newModule) {
            return null; //TODO: Better return not nullable type Module and thorw an exception
        }

        $this->install($newModule);

        return $newModule;
    }

    public function updateWithDependencies(Module $module): ?Module
    {
        $newModule = $this->update($module);
        if (!$newModule) {
            return null; //TODO: Better return not nullable type Module and thorw an exception
        }

        $this->installDependencies($newModule);
        return $newModule;
    }

    public function installFile(string $src, string $dest, bool $overwrite = false): bool
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

    public function uninstallFile(string $dest): void
    {
        if (file_exists($dest)) {
            unlink($dest);
        }
    }
}
