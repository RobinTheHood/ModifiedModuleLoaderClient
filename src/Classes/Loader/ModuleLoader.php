<?php
namespace RobinTheHood\ModifiedModuleLoaderClient\Loader;

use RobinTheHood\ModifiedModuleLoaderClient\ModuleFilter;

class ModuleLoader
{
    private static $moduleLoader = null;
    private $modules;

    public static function getModuleLoader()
    {
        if (!self::$moduleLoader) {
            self::$moduleLoader = new ModuleLoader();
        }
        return self::$moduleLoader;
    }

    public function loadAll()
    {
        if (!isset($this->modules)) {
            $remoteModuleLoader = RemoteModuleLoader::getModuleLoader();
            $remoteModules = $remoteModuleLoader->loadAll();

            $localModuleLoader = LocalModuleLoader::getModuleLoader();
            $localModules = $localModuleLoader->loadAll();

            $modules = array_merge($localModules, $remoteModules);
            $modules = ModuleFilter::filterValid($modules);
            $this->modules = $modules;
        }

        return $this->modules;
    }

    public function loadAllByArchiveName($archiveName)
    {
        $modules = $this->loadAll();
        $modules = ModuleFilter::filterByArchiveName($modules, $archiveName);
        return $modules;
    }

    public function loadByArchiveName($archiveName, $version = null)
    {
        $moduleLoader = LocalModuleLoader::getModuleLoader();
        $module = $moduleLoader->loadByArchiveName($archiveName, $version);

        if (!$module) {
            $moduleLoader = RemoteModuleLoader::getModuleLoader();
            $module = $moduleLoader->loadByArchiveName($archiveName, $version);
        }

        return $module;
    }
}
