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

use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\Api\V1\ApiRequest;

class CachedRemoteModuleLoader extends RemoteModuleLoader
{
    private static $cachedModuleLoader = null;

    public static function getCachedModuleLoader(): CachedRemoteModuleLoader
    {
        if (!self::$cachedModuleLoader) {
            self::$cachedModuleLoader = new CachedRemoteModuleLoader(
                new ApiRequest(),
                new ApiV1ModuleConverter()
            );
        }
        return self::$cachedModuleLoader;
    }

    private function getCache(string $filePath): array
    {
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $result = unserialize($content);
            $modules = $this->moduleConverter->convertToModules($result);
            return $modules;
        }
        return [];
    }

    private function setCache(string $filePath, array $content): void
    {
        $contentAsString = serialize($content);
        file_put_contents($filePath, $contentAsString);
    }


    /**
     * Loads all remote module versions.
     *
     * @return Module[] Returns a array of module versions.
     */
    public function loadAllVersions(): array
    {
        $cacheName = md5('loadAllVersions');
        $cacheFilePath = App::getRoot() . '/cache/api/' .  $cacheName;

        $modules = $this->getCache($cacheFilePath);
        if ($modules) {
            return $modules;
        }

        return parent::loadAllVersions();
    }

    /**
     * Loads all latest remote module versions.
     *
     * @return Module[] Returns a array of module versions.
     */
    public function loadAllLatestVersions(): array
    {
        $cacheName = md5('loadAllLatestVersions');
        $cacheFilePath = App::getRoot() . '/cache/api/' .  $cacheName;

        $modules = $this->getCache($cacheFilePath);
        if ($modules) {
            return $modules;
        }

        return parent::loadAllLatestVersions();
    }

    /**
     * Loads all module versions by a given archiveName.
     *
     * @return Module[] Returns a array of module versions.
     */
    public function loadAllVersionsByArchiveName(string $archiveName): array
    {
        $cacheName = md5('loadAllVersionsByArchiveName' . $archiveName);
        $cacheFilePath = App::getRoot() . '/cache/api/' .  $cacheName;

        $modules = $this->getCache($cacheFilePath);
        if ($modules) {
            return $modules;
        }

        return parent::loadAllVersionsByArchiveName($archiveName);
    }

    /**
     * Loads the latest remote module version by a given archiveName.
     *
     * @return Module|null Returns a module version or null.
     */
    public function loadLatestVersionByArchiveName(string $archiveName): ?Module
    {
        $cacheName = md5('loadLatestVersionByArchiveName' . $archiveName);
        $cacheFilePath = App::getRoot() . '/cache/api/' .  $cacheName;

        $modules = $this->getCache($cacheFilePath);
        if ($modules) {
            return $modules[0];
        }

        return parent::loadLatestVersionByArchiveName($archiveName);
    }


    /**
     * Loads a remote module version by a given archiveName and version.
     *
     * @return Module|null Returns a module version or null.
     */
    public function loadByArchiveNameAndVersion(string $archiveName, string $version): ?Module
    {
        $cacheName = md5('loadByArchiveNameAndVersion' . $archiveName . $version);
        $cacheFilePath = App::getRoot() . '/cache/api/' .  $cacheName;

        $modules = $this->getCache($cacheFilePath);
        if ($modules) {
            return $modules[0];
        }

        return parent::loadByArchiveNameAndVersion($archiveName, $version);
    }
}
