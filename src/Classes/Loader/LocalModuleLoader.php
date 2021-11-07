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

namespace RobinTheHood\ModifiedModuleLoaderClient\Loader;

use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFactory;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFilter;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;

class LocalModuleLoader
{
    private static $moduleLoader = null;
    private $modules;

    public static function getModuleLoader(): LocalModuleLoader
    {
        if (!self::$moduleLoader) {
            self::$moduleLoader = new LocalModuleLoader();
        }
        return self::$moduleLoader;
    }

    /**
     * Resets / deletes allready loaded modules data. For examplae because
     * during the script runtime the amount of modules or data of modules
     * changed and the LocalModuleLoader does not give the latest module
     * informations.
     */
    public function resetCache()
    {
        $this->modules = null;
    }

    /**
     * Loads all local module versions.
     *
     * @return Module[] Returns a array of module versions.
     */
    public function loadAllVersions(): array
    {
        if (isset($this->modules)) {
            return $this->modules;
        }

        $moduleDirs = $this->getModuleDirs();

        $modules = [];
        foreach ($moduleDirs as $moduleDir) {
            try {
                $module = ModuleFactory::createFromPath($moduleDir);
                $modules[] = $module;
            } catch (\RuntimeException $e) {
                // do nothing
            }
        }

        $this->modules = $modules;
        return $this->modules;
    }

    /**
     * Loads all local module versions by a given archiveName.
     *
     * @return Module[] Returns a array of module versions.
     */
    public function loadAllVersionsByArchiveName(string $archiveName): array
    {
        $modules = $this->loadAllVersions();
        $modules = ModuleFilter::filterByArchiveName($modules, $archiveName);
        return $modules;
    }

    /**
     * Loads a local module version by a given archiveName and version.
     *
     * @return Module|null Returns a module version or null.
     */
    public function loadByArchiveNameAndVersion(string $archiveName, string $version): ?Module
    {
        $modules = $this->loadAllVersions();
        $module = ModuleFilter::getByArchiveNameAndVersion($modules, $archiveName, $version);
        return $module;
    }

    /**
     * Loads the latest local module version by a given archiveName.
     *
     * @return Module|null Returns a module version or null.
     */
    public function loadLatestVersionByArchiveName(string $archiveName): ?Module
    {
        $modules = $this->loadAllVersionsByArchiveName($archiveName);
        $module = ModuleFilter::getLatestVersion($modules);
        return $module;
    }

    /**
     * Loads all installed module versions.
     *
     * @return Module[] Returns a array of installed module versions.
     */
    public function loadAllInstalledVersions(): array
    {
        $modules = $this->loadAllVersions();
        $installedModules = ModuleFilter::filterInstalled($modules);
        return $installedModules;
    }

    public function getVendorDirs()
    {
        return FileHelper::scanDir(App::getModulesRoot(), FileHelper::DIRS_ONLY);
    }

    public function getModuleDirs()
    {
        return FileHelper::scanDirRecursive(App::getModulesRoot(), FileHelper::DIRS_ONLY, false, 3);
    }
}
