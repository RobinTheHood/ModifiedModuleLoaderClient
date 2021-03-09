<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient\Loader;

use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFilter;

class ModuleLoader
{
    private static $moduleLoader = null;
    private $cachedModules;

    public static function getModuleLoader(): ModuleLoader
    {
        if (!self::$moduleLoader) {
            self::$moduleLoader = new ModuleLoader();
        }
        return self::$moduleLoader;
    }

    /**
     * Loads all local module version plus all latest remote module version.
     *
     * @return Module[] Returns a array of module versions.
     */
    public function loadAllVersionsWithLatestRemote(): array
    {
        if (isset($this->cachedModules)) {
            return $this->cachedModules;
        }

        $remoteModuleLoader = RemoteModuleLoader::getModuleLoader();
        $remoteModules = $remoteModuleLoader->loadAllLatestVersions();

        $localModuleLoader = LocalModuleLoader::getModuleLoader();
        $localModules = $localModuleLoader->loadAllVersions();

        $modules = array_merge($localModules, $remoteModules);
        $modules = ModuleFilter::filterValid($modules);
        $this->cachedModules = $modules;

        return $this->cachedModules;
    }

    /**
     * Loads all local module versions plus all remote module versions by a given archiveName.
     *
     * @return Module[] Returns a array of module versions.
     */
    public function loadAllVersionsByArchiveName(string $archiveName): array
    {
        $remoteModuleLoader = RemoteModuleLoader::getModuleLoader();
        $remoteModules = $remoteModuleLoader->loadAllVersionsByArchiveName($archiveName);

        $localModuleLoader = LocalModuleLoader::getModuleLoader();
        $localModules = $localModuleLoader->loadAllVersionsByArchiveName($archiveName);

        $modules = array_merge($localModules, $remoteModules);
        $modules = ModuleFilter::filterValid($modules);

        return $modules;
    }

    /**
     * Loads all local module versions plus the latest remote module version by a given archiveName.
     *
     * @return Module[] Returns a array of module versions.
     */
    public function loadAllVersionsByArchiveNameWithLatestRemote(string $archiveName): array
    {
        $remoteModuleLoader = RemoteModuleLoader::getModuleLoader();
        $remoteModule = $remoteModuleLoader->loadLatestVersionByArchiveName($archiveName);

        $localModuleLoader = LocalModuleLoader::getModuleLoader();
        $localModules = $localModuleLoader->loadAllVersionsByArchiveName($archiveName);

        $modules = $localModules;
        if ($remoteModule) {
            $modules[] = $remoteModule;
        }

        $modules = ModuleFilter::filterValid($modules);
        return $modules;
    }

    /**
     * Loads a module version by a given archiveName and version from local or remote. If no local module is found,
     * a remote module is searched for.
     *
     * @return Module|null Returns a module version or null.
     */
    public function loadByArchiveNameAndVersion(string $archiveName, string $version): ?Module
    {
        $moduleLoader = LocalModuleLoader::getModuleLoader();
        $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if ($module) {
            return $module;
        }

        $moduleLoader = RemoteModuleLoader::getModuleLoader();
        $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);
        return $module;
    }

    /**
     * Loads the latest local or remote versions by a given archiveName and version constraint.
     *
     * @return Module[] Returns a array of module versions.
     */
    public function loadByArchiveNameAndVersionContraint(string $archiveName, string $versionConstraint): array
    {
        $modules = $this->loadAllVersionsByArchiveName($archiveName);
        $modules = ModuleFilter::filterByVersionConstrain($modules, $versionConstraint);
        return $modules;
    }
}
