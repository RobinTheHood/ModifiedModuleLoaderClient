<?php
namespace RobinTheHood\ModifiedModuleLoaderClient\Loader;

use RobinTheHood\ModifiedModuleLoaderClient\ModuleFilter;

class ModuleLoader
{
    private static $moduleLoader = null;
    private $cachedModules;

    public static function getModuleLoader()
    {
        if (!self::$moduleLoader) {
            self::$moduleLoader = new ModuleLoader();
        }
        return self::$moduleLoader;
    }


    public function loadAllVersionsWithLatestRemote()
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

    public function loadAllVersionsByArchiveName($archiveName)
    {
        $remoteModuleLoader = RemoteModuleLoader::getModuleLoader();
        $remoteModules = $remoteModuleLoader->loadAllVersionsByArchiveName($archiveName);

        $localModuleLoader = LocalModuleLoader::getModuleLoader();
        $localModules = $localModuleLoader->loadAllVersionsByArchiveName($archiveName);

        $modules = array_merge($localModules, $remoteModules);
        $modules = ModuleFilter::filterValid($modules);

        return $modules;
    }

    public function loadAllVersionsByArchiveNameWithLatestRemote($archiveName)
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
        //$modules = ModuleFilter::filterByArchiveName($modules, $archiveName);
        return $modules;
    }

    public function loadByArchiveNameAndVersion($archiveName, $version)
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
}
