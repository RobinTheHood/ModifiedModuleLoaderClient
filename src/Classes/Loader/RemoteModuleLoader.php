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
use RobinTheHood\ModifiedModuleLoaderClient\Api\Client\ApiRequest;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\ArrayHelper;
use RobinTheHood\ModifiedModuleLoaderClient\Api\Exceptions\UrlNotExistsApiException;

class RemoteModuleLoader
{
    private static $moduleLoader = null;
    
    private $cachedModules;

    public static function getModuleLoader(): RemoteModuleLoader
    {
        if (!self::$moduleLoader) {
            self::$moduleLoader = new RemoteModuleLoader();
        }
        return self::$moduleLoader;
    }

    /**
     * Loads all remote module versions.
     * 
     * @return Modules[] Returns a array of module versions.
     */
    public function loadAllVersions(): array
    {
        $apiRequest = new ApiRequest();
        $result = $apiRequest->getAllModuleVersions();
        $modules = $this->convertResultToModules($result);
        return $modules;
    }

    /**
     * Loads all latest remote module versions.
     * 
     * @return Modules[] Returns a array of module versions.
     */
    public function loadAllLatestVersions(): array
    {
        if (isset($this->cachedModules)) {
            return $this->cachedModules;
        }

        $apiRequest = new ApiRequest();
        $result = $apiRequest->getModules(['filter' => 'latestVersion']);
        $modules = $this->convertResultToModules($result);

        $this->cachedModules = $modules;
        return $this->cachedModules;
    }

    /**
     * Loads all module versions by a given archiveName.
     * 
     * @return Modules[] Returns a array of module versions.
     */
    public function loadAllVersionsByArchiveName(string $archiveName): array
    {
        $apiRequest = new ApiRequest();
        $result = $apiRequest->getModules(['archiveName' => $archiveName]);
        $modules = $this->convertResultToModules($result);
        return $modules;
    }

    /**
     * Loads the latest remote module version by a given archiveName.
     * 
     * @return Module|null Returns a module version or null.
     */
    public function loadLatestVersionByArchiveName(string $archiveName): ?Module
    {
        $apiRequest = new ApiRequest();
        $result = $apiRequest->getModules(['filter' => 'latestVersion', 'archiveName' => $archiveName]);
        $modules = $this->convertResultToModules($result);
        return ArrayHelper::getIfSet($modules, 0, null);
    }


    /**
     * Loads a remote module version by a given archiveName and version.
     * 
     * @return Module|null Returns a module version or null.
     */
    public function loadByArchiveNameAndVersion(string $archiveName, string $version): ?Module
    {
        $apiRequest = new ApiRequest();
        $result = $apiRequest->getModules(['archiveName' => $archiveName, 'version' => $version]);
        $modules = $this->convertResultToModules($result);
        return ArrayHelper::getIfSet($modules, 0, null);
    }


    public function convertResultToModules($result)
    {
        if (!ArrayHelper::getIfSet($result, 'content')) {
            return [];
        }

        $modules = [];
        foreach($result['content'] as $moduleArray) {
            $module = new Module();
            $module->loadFromArray($moduleArray);
            $modules[] = $module;
        }

        return $modules;
    }
}
