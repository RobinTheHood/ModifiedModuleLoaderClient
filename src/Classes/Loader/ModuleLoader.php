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

use RobinTheHood\ModifiedModuleLoaderClient\Config;
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFilter;

class ModuleLoader
{
    /** @var Module[] */
    private static $cachedModules = [];

    /** @var LocalModuleLoader */
    private $localModuleLoader;

    /** @var RemoteModuleLoader */
    private $remoteModuleLoader;

    /** @var ModuleFilter */
    private $moduleFilter;

    public static function create(int $mode): ModuleLoader
    {
        $moduleFilter = ModuleFilter::create($mode);
        $localModuleLoader = LocalModuleLoader::create($mode);
        $remoteModuleLoader = RemoteModuleLoader::create();
        $moduleLoader = new ModuleLoader($localModuleLoader, $remoteModuleLoader, $moduleFilter);
        return $moduleLoader;
    }

    public static function createFromConfig(): ModuleLoader
    {
        return self::create(Config::getDependenyMode());
    }

    public function __construct(
        LocalModuleLoader $localModuleLoader,
        RemoteModuleLoader $remoteModuleLoader,
        ModuleFilter $moduleFilter
    ) {
        $this->localModuleLoader = $localModuleLoader;
        $this->remoteModuleLoader = $remoteModuleLoader;
        $this->moduleFilter = $moduleFilter;
    }

    /**
     * Resets / deletes allready loaded modules data. For examplae because
     * during the script runtime the amount of modules or data of modules
     * changed and the LocalModuleLoader does not give the latest module
     * informations.
     */
    public function resetCache()
    {
        self::$cachedModules = [];
        $this->localModuleLoader->resetCache();
        $this->remoteModuleLoader->resetCache();
    }

    /**
     * Loads all local module version plus all latest remote module version.
     *
     * @return Module[] Returns a array of module versions.
     */
    public function loadAllVersionsWithLatestRemote(): array
    {
        if (self::$cachedModules) {
            return self::$cachedModules;
        }

        $remoteModules = $this->remoteModuleLoader->loadAllLatestVersions();
        $localModules = $this->localModuleLoader->loadAllVersions();

        $modules = array_merge($localModules, $remoteModules);
        $modules = $this->moduleFilter->filterValid($modules);
        self::$cachedModules = $modules;

        return self::$cachedModules;
    }

    /**
     * Loads all local module versions plus all remote module versions by a given archiveName.
     *
     * @return Module[] Returns a array of module versions.
     */
    public function loadAllVersionsByArchiveName(string $archiveName): array
    {
        $remoteModules = $this->remoteModuleLoader->loadAllVersionsByArchiveName($archiveName);
        $localModules = $this->localModuleLoader->loadAllVersionsByArchiveName($archiveName);

        $modules = array_merge($localModules, $remoteModules);
        $modules = $this->moduleFilter->filterValid($modules);

        return $modules;
    }

    /**
     * Loads all local module versions plus the latest remote module version by a given archiveName.
     *
     * @return Module[] Returns a array of module versions.
     */
    public function loadAllVersionsByArchiveNameWithLatestRemote(string $archiveName): array
    {
        $remoteModule = $this->remoteModuleLoader->loadLatestVersionByArchiveName($archiveName);
        $localModules = $this->localModuleLoader->loadAllVersionsByArchiveName($archiveName);

        $modules = $localModules;
        if ($remoteModule) {
            $modules[] = $remoteModule;
        }

        $modules = $this->moduleFilter->filterValid($modules);
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
        $module = $this->localModuleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if ($module) {
            return $module;
        }

        $module = $this->remoteModuleLoader->loadByArchiveNameAndVersion($archiveName, $version);
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
        $module = $this->localModuleLoader->loadLatestVersionByArchiveName($archiveName);
        if ($module) {
            $modules[] = $module;
        }

        $module = $this->remoteModuleLoader->loadLatestVersionByArchiveName($archiveName);
        if ($module) {
            $modules[] = $module;
        }

        $latestVersion = $this->moduleFilter->getLatestVersion($modules);
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
        $modules = $this->moduleFilter->filterByVersionConstrain($modules, $versionConstraint);
        return $modules;
    }

    /**
     * Loads a latest local or remote version by a given archiveName that fits the version constraint.
     *
     */
    public function loadLatestByArchiveNameAndConstraint(string $archiveName, string $versionConstraint): ?Module
    {
        $modules = $this->loadAllByArchiveNameAndConstraint($archiveName, $versionConstraint);
        $module = $this->moduleFilter->getLatestVersion($modules);
        return $module;
    }
}
