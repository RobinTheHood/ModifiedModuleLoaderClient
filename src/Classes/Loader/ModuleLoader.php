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
     * Loads the latest local or remote version of an Module by a given archiveName.
     *
     * @return Module|null Returns a module version or null.
     */
    public function loadLatestVersionByArchiveName(string $archiveName): ?Module
    {
        $modules = [];
        $localModuleLoader = LocalModuleLoader::getModuleLoader();
        $module = $localModuleLoader->loadLatestVersionByArchiveName($archiveName);
        if ($module) {
            $modules[] = $module;
        }

        $remoteModuleLoader = RemoteModuleLoader::getModuleLoader();
        $module = $remoteModuleLoader->loadLatestVersionByArchiveName($archiveName);
        if ($module) {
            $modules[] = $module;
        }

        $latestVersion = ModuleFilter::getLatestVersion($modules);
        return $latestVersion;
    }

    /**
     * Loads local or remote versions by a given archiveName that fits the version constraint.
     *
     * @return Module[] Returns a array of module versions.
     */
    public function loadAllByArchiveNameAndConstraint(string $archiveName, string $versionConstraint): array
    {
        $modules = $this->loadAllVersionsByArchiveName($archiveName);
        $modules = ModuleFilter::filterByVersionConstrain($modules, $versionConstraint);
        return $modules;
    }

    /**
     * Loads a latest local or remote version by a given archiveName that fits the version constraint.
     *
     */
    public function loadLatestByArchiveNameAndConstraint(string $archiveName, string $versionConstraint): ?Module
    {
        $modules = $this->loadAllByArchiveNameAndConstraint($archiveName, $versionConstraint);
        $module = ModuleFilter::getLatestVersion($modules);
        return $module;
    }
}
