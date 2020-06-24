<?php
namespace RobinTheHood\ModifiedModuleLoaderClient\Loader;

use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFilter;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;

class LocalModuleLoader
{
    private static $moduleLoader = null;
    private $modules;

    public static function getModuleLoader()
    {
        if (!self::$moduleLoader) {
            self::$moduleLoader = new LocalModuleLoader();
        }
        return self::$moduleLoader;
    }

    public function loadAllVersions()
    {
        if (isset($this->modules)) {
            return $this->modules;
        }
        
        $moduleDirs = $this->getModuleDirs();

        $modules = [];
        foreach($moduleDirs as $moduleDir) {
            $module = new Module();
            if ($module->load($moduleDir)) {
                $modules[] = $module;
            }
        }

        $this->modules = $modules;
        return $this->modules;
    }

    public function loadAllVersionsByArchiveName($archiveName)
    {
        $modules = $this->loadAllVersions();
        $modules = ModuleFilter::filterByArchiveName($modules, $archiveName);
        return $modules;
    }

    public function loadByArchiveNameAndVersion($archiveName, $version)
    {
        $modules = $this->loadAllVersions();
        $module = ModuleFilter::getByArchiveNameAndVersion($modules, $archiveName, $version);
        return $module;
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
