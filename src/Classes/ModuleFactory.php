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

namespace RobinTheHood\ModifiedModuleLoaderClient;

use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\ServerHelper;

class ModuleFactory
{
    private const DIR_MODULE_SRC = 'src';
    private const DIR_MODULE_SRC_MMLC = "src-mmlc";

    public static function createFromPath(string $path): Module
    {
        $moduleInfoJsonPath = $path . '/moduleinfo.json';

        if (!file_exists($moduleInfoJsonPath)) {
            throw new \RuntimeException('moduleinfo.json not exists');
        }

        $json = file_get_contents($moduleInfoJsonPath);
        $array = json_decode($json, true);

        if (!$array) {
            throw new \RuntimeException('Can not parse ' . $moduleInfoJsonPath);
        }

        $sourceDir = $array['sourceDir'] ?? self::DIR_MODULE_SRC;
        $sourceMmlcDir = $array['sourceMmlcDir'] ?? self::DIR_MODULE_SRC_MMLC;

        $modulePath = FileHelper::stripBasePath(App::getRoot(), $path);
        $category = $array['category'] ?? '';
        $localRootPath = App::getRoot();
        $absSrcRootPath = $localRootPath . $modulePath . '/' . $sourceDir;
        $absSrcMmlcRootPath = $localRootPath . $modulePath . '/' . $sourceMmlcDir;

        $array['localRootPath'] = $localRootPath;
        $array['urlRootPath'] = ServerHelper::getUri();
        $array['modulePath'] = $modulePath;
        $array['iconPath'] = self::createIconPath($modulePath, $path, $category);
        $array['imagePaths'] = self::createImagePaths($path . '/images');
        $array['docFilePaths'] = self::createDocFilePaths($path . '/docs');
        $array['changelogPath'] = self::createChangelogPath($modulePath, $path);
        $array['readmePath'] = self::createReadmePath($modulePath, $path);
        $array['srcFilePaths'] = self::createSrcFilePaths($absSrcRootPath);
        $array['srcMmlcFilePaths'] = self::createSrcFilePaths($absSrcMmlcRootPath);
        $array['isRemote'] = false;

        $module = self::createFromArray($array);

        return $module;
    }

    public static function createFromArray(array $array): Module
    {
        $module = new Module();

        $autoload = $array['autoload'] ?? [];
        if (!$autoload) {
            $autoload = [];
        }

        // ModuleInfo
        $module->setName($array['name'] ?? '');
        $module->setArchiveName($array['archiveName'] ?? '');
        $module->setSourceDir($array['sourceDir'] ?? self::DIR_MODULE_SRC);
        $module->setSourceMmlcDir($array['sourceMmlcDir'] ?? self::DIR_MODULE_SRC_MMLC);
        $module->setVersion($array['version'] ?? 'auto');
        $module->setDate($array['date'] ?? 'unknown');
        $module->setShortDescription($array['shortDescription'] ?? '');
        $module->setDescription($array['description'] ?? '');
        $module->setDeveloper($array['developer'] ?? '');
        $module->setDeveloperWebsite($array['developerWebsite'] ?? '');
        $module->setWebsite($array['website'] ?? '');
        $module->setRequire($array['require'] ?? []);
        $module->setCategory($array['category'] ?? '');
        $module->setType($array['type'] ?? '');
        $module->setModifiedCompatibility($array['modifiedCompatibility'] ?? []);
        $module->setInstallation($array['installation'] ?? '');
        $module->setVisibility($array['visibility'] ?? '');
        $module->setPrice($array['price'] ?? '');
        $module->setAutoload($autoload);
        $module->setTags($array['tags'] ?? '');
        $module->setPhp($array['php'] ?? []);
        $module->setMmlc($array['mmlc'] ?? ['version' => '^' . App::getMmlcVersion()]);

        // Module
        $module->setLocalRootPath($array['localRootPath'] ?? '');
        $module->setUrlRootPath($array['urlRootPath'] ?? '');
        $module->setModulePath($array['modulePath'] ?? '');
        $module->setIconPath($array['iconPath'] ?? '');
        $module->setImagePaths($array['imagePaths'] ?? []);
        $module->setDocFilePaths($array['docFilePaths'] ?? []);
        $module->setChangelogPath($array['changelogPath'] ?? '');
        $module->setReadmePath($array['readmePath'] ?? '');
        $module->setSrcFilePaths($array['srcFilePaths'] ?? []);
        $module->setSrcMmlcFilePaths($array['srcMmlcFilePaths'] ?? []);
        $module->setRemote($array['isRemote'] ?? false);
        $module->setLoadable($array['isLoadable'] ?? false);

        return $module;
    }

    private static function createIconPath(string $modulePath, string $path, string $category): string
    {
        if (file_exists($path . '/icon.jpg')) {
            $iconPath = $modulePath . '/icon.jpg';
        } elseif (file_exists($path . '/icon.png')) {
            $iconPath = $modulePath . '/icon.png';
        } else {
            if ($category == 'library') {
                $iconPath = '/src/Templates/Images/icon_library.png';
            } else {
                $iconPath = '/src/Templates/Images/icon_module.png';
            }
        }
        return $iconPath;
    }

    private static function createImagePaths(string $path): array
    {
        if (!is_dir($path)) {
            return [];
        }

        $images = [];

        $fileNames = scandir($path);
        foreach ($fileNames as $fileName) {
            if (strpos($fileName, '.jpg') || strpos($fileName, '.png')) {
                $images[] = $path . '/' . $fileName;
            }
        }

        $images = FileHelper::stripAllBasePaths(App::getRoot(), $images);

        return $images;
    }

    private static function createDocFilePaths(string $path): array
    {
        if (!is_dir($path)) {
            return [];
        }

        $docFiles = [];

        $fileNames = scandir($path);
        foreach ($fileNames as $fileName) {
            if (strpos($fileName, '.md')) {
                $docFiles[] = $path . '/' . $fileName;
            }
        }

        $docFiles = FileHelper::stripAllBasePaths(App::getRoot(), $docFiles);

        return $docFiles;
    }

    private static function createChangeLogPath(string $modulePath, string $path): string
    {
        $changelogPath = '';
        if (file_exists($path . '/changelog.md')) {
            $changelogPath = $modulePath . '/changelog.md';
        }
        return $changelogPath;
    }

    private static function createReadmePath(string $modulePath, string $path): string
    {
        $readmePath = '';
        if (file_exists($path . '/README.md')) {
            $readmePath = $modulePath . '/README.md';
        } elseif (file_exists($path . '/readme.md')) {
            $readmePath = $modulePath . '/readme.md';
        }
        return $readmePath;
    }

    private static function createSrcFilePaths(string $path): array
    {
        $filePaths = FileHelper::scanDirRecursive(
            $path,
            FileHelper::FILES_ONLY,
            true
        );
        $filePaths = FileHelper::stripAllBasePaths($path, $filePaths);
        return $filePaths;
    }
}
