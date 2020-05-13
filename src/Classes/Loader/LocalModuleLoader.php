<?php
namespace RobinTheHood\ModifiedModuleLoaderClient\Loader;

use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFilter;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\ArrayHelper;

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

    public function loadAll()
    {
        if (!isset($this->modules)) {
            $modules = [];

            $moduleDirs = $this->getModuleDirs();

            foreach($moduleDirs as $moduleDir) {
                $module = new Module();
                if ($module->load($moduleDir)) {
                    $modules[] = $module;
                }
            }

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
        $modules = $this->loadAllByArchiveName($archiveName);

        if (!$version) {
            $module = ModuleFilter::getNewestVersion($modules);
        } else {
            $modules = ModuleFilter::filterByVersionConstrain($modules, $version);
            $module = ModuleFilter::getNewestVersion($modules);
        }

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
