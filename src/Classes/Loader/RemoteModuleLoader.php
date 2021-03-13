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
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFactory;
use RobinTheHood\ModifiedModuleLoaderClient\Api\V1\ApiRequest;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\ArrayHelper;

class RemoteModuleLoader
{
    private static $moduleLoader = null;
    
    private $cachedModules;

    /** @var ApiRequest */
    private $apiRequest;

    public function __construct(ApiRequest $apiRequest)
    {
        $this->apiRequest = $apiRequest;
    }

    public static function getModuleLoader(): RemoteModuleLoader
    {
        if (!self::$moduleLoader) {
            self::$moduleLoader = new RemoteModuleLoader(new ApiRequest());
        }
        return self::$moduleLoader;
    }

    /**
     * Loads all remote module versions.
     *
     * @return Module[] Returns a array of module versions.
     */
    public function loadAllVersions(): array
    {
        $result = $this->apiRequest->getModules([]);
        $modules = $this->convertResultToModules($result);
        return $modules;
    }

    /**
     * Loads all latest remote module versions.
     *
     * @return Module[] Returns a array of module versions.
     */
    public function loadAllLatestVersions(): array
    {
        if (isset($this->cachedModules)) {
            return $this->cachedModules;
        }

        $result = $this->apiRequest->getModules(['filter' => 'latestVersion']);
        $modules = $this->convertResultToModules($result);

        $this->cachedModules = $modules;
        return $this->cachedModules;
    }

    /**
     * Loads all module versions by a given archiveName.
     *
     * @return Module[] Returns a array of module versions.
     */
    public function loadAllVersionsByArchiveName(string $archiveName): array
    {
        $result = $this->apiRequest->getModules(['archiveName' => $archiveName]);
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
        $result = $this->apiRequest->getModules(['filter' => 'latestVersion', 'archiveName' => $archiveName]);
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
        $result = $this->apiRequest->getModules(['archiveName' => $archiveName, 'version' => $version]);
        $modules = $this->convertResultToModules($result);
        return ArrayHelper::getIfSet($modules, 0, null);
    }


    public function convertResultToModules($result)
    {
        if (!ArrayHelper::getIfSet($result, 'content')) {
            return [];
        }

        $modules = [];
        foreach ($result['content'] as $moduleArray) {
            try {
                $module = ModuleFactory::createFromArray($moduleArray);
                $modules[] = $module;
            } catch (\RuntimeException $e) {
                // do nothing
            }
        }

        return $modules;
    }
}
