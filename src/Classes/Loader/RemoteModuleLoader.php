<?php
namespace RobinTheHood\ModifiedModuleLoaderClient\Loader;


use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFilter;
use RobinTheHood\ModifiedModuleLoaderClient\Api\Client\ApiRequest;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\ArrayHelper;
use RobinTheHood\ModifiedModuleLoaderClient\Api\Exceptions\UrlNotExistsApiException;

class RemoteModuleLoader
{
    private static $moduleLoader = null;
    private $modules;

    public static function getModuleLoader()
    {
        if (!self::$moduleLoader) {
            self::$moduleLoader = new RemoteModuleLoader();
        }
        return self::$moduleLoader;
    }

    public function loadAll()
    {
        if (!isset($this->modules)) {
            $apiRequest = new ApiRequest();
            $result = $apiRequest->getAllModules();

            if (!ArrayHelper::getIfSet($result, 'content')) {
                return [];
            }

            $modules = [];
            foreach($result['content'] as $moduleArray) {
                $module = new Module();
                $module->loadFromArray($moduleArray);
                $modules[] = $module;
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
}
