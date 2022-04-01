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

use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\Api\V1\ApiRequest;

class RemoteModuleLoader
{
    private static $moduleLoader = null;

    private $cachedModules;

    /** @var ApiRequest */
    private $apiRequest;

    /** @var ApiV1ModuleConverter */
    protected $moduleConverter;

    public function __construct(ApiRequest $apiRequest, ApiV1ModuleConverter $moduleConverter)
    {
        $this->apiRequest = $apiRequest;
        $this->moduleConverter = $moduleConverter;
    }

    public static function getModuleLoader(): RemoteModuleLoader
    {
        if (!self::$moduleLoader) {
            self::$moduleLoader = new RemoteModuleLoader(
                new ApiRequest(),
                new ApiV1ModuleConverter()
            );
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
        $modules = $this->moduleConverter->convertToModules($result);
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
        $modules = $this->moduleConverter->convertToModules($result);

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
        $modules = $this->moduleConverter->convertToModules($result);
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
        $modules = $this->moduleConverter->convertToModules($result);
        return $modules[0] ?? null;
    }


    /**
     * Loads a remote module version by a given archiveName and version.
     *
     * @return Module|null Returns a module version or null.
     */
    public function loadByArchiveNameAndVersion(string $archiveName, string $version): ?Module
    {
        $result = $this->apiRequest->getModules(['archiveName' => $archiveName, 'version' => $version]);
        $modules = $this->moduleConverter->convertToModules($result);
        return $modules[0] ?? null;
    }
}
