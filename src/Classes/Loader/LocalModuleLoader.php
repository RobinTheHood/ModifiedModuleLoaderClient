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
use RobinTheHood\ModifiedModuleLoaderClient\Config;
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFactory;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFilter;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;

class LocalModuleLoader
{
    /** @var Module[] */
    private static $cachedModules = [];

    /** @var ModuleFilter */
    private $moduleFilter;

    /** @var string */
    private $modulesRootPath;

    public static function create(int $mode): LocalModuleLoader
    {
        $moduleFilter = ModuleFilter::create($mode);
        $localModuleLoader = new LocalModuleLoader($moduleFilter);
        return $localModuleLoader;
    }

    public static function createFromConfig(): LocalModuleLoader
    {
        return self::create(Config::getDependenyMode());
    }

    public function __construct(ModuleFilter $moduleFilter)
    {
        $this->moduleFilter = $moduleFilter;
        $this->modulesRootPath = App::getModulesRoot();
    }

    public function setModulesRootPath(string $path): void
    {
        $this->modulesRootPath = $path;
    }

    /**
     * Resets / deletes allready loaded modules data. For examplae because
     * during the script runtime the amount of modules or data of modules
     * changed and the LocalModuleLoader does not give the latest module
     * informations.
     */
    public function resetCache()
    {
        self::$cachedModules = null;
    }

    /**
     * Loads all local module versions.
     *
     * @return Module[] Returns a array of module versions.
     */
    public function loadAllVersions(): array
    {
        if (self::$cachedModules) {
            return self::$cachedModules;
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

        self::$cachedModules = $modules;
        return self::$cachedModules;
    }

    /**
     * Loads all local module versions by a given archiveName.
     *
     * @return Module[] Returns a array of module versions.
     */
    public function loadAllVersionsByArchiveName(string $archiveName): array
    {
        $modules = $this->loadAllVersions();
        $modules = $this->moduleFilter->filterByArchiveName($modules, $archiveName);
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
        $module = $this->moduleFilter->getByArchiveNameAndVersion($modules, $archiveName, $version);
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
        $module = $this->moduleFilter->getLatestVersion($modules);
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
        $installedModules = $this->moduleFilter->filterInstalled($modules);
        return $installedModules;
    }

    /**
     * Loads the installed module version by a given archiveName
     *
     * @return Module|null Returns a array of installed module versions.
     */
    public function loadInstalledVersionByArchiveName(string $arhciveName): ?Module
    {
        $modules = $this->loadAllVersions();
        $installedModules = $this->moduleFilter->filterInstalled($modules);
        $installedModules = $this->moduleFilter->filterByArchiveName($installedModules, $arhciveName);
        return $installedModules[0] ?? null;
    }

    public function getVendorDirs()
    {
        return FileHelper::scanDir($this->modulesRootPath, FileHelper::DIRS_ONLY);
    }

    public function getModuleDirs()
    {
        return FileHelper::scanDirRecursive($this->modulesRootPath, FileHelper::DIRS_ONLY, false, 3);
    }
}
