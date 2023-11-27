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

use Exception;
use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;

class ShopInfo
{
    public static function getAdminPath(): string
    {
        return App::getShopRoot() . '/' . self::getAdminDir();
    }

    public static function getAdminUrl(): string
    {
        $httpHost = $_SERVER['HTTP_HOST'] ?? '';
        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';

        $host = rtrim($httpHost, '/');
        $path = str_replace($documentRoot, '', ShopInfo::getAdminPath());
        $path = ltrim($path, '/');
        return '//' . $host . '/' . $path;
    }

    /**
     * @return string Returns the installed modified version as string.
     */
    public static function getModifiedVersion(): string
    {
        $path = self::getAdminPath() . '/includes/version.php';

        if (!file_exists($path)) {
            return 'unknown';
        }

        $fileStr = file_get_contents($path);

        // Try DB_VERSION
        $pattern = "/MOD_(\d+\.\d+\.\d+(\.\d+)?)\'\);/";
        if (preg_match($pattern, $fileStr, $matches)) {
            return $matches[1];
        }

        // Try MAJOR_VERSION ans MINOR_VERSION
        preg_match('/MAJOR_VERSION.+?\'([\d\.]+)\'/', $fileStr, $versionMajor);
        preg_match('/MINOR_VERSION.+?\'([\d\.]+)\'/', $fileStr, $versionMinor);
        $versionMajor[1] = $versionMajor[1] ?? '';
        $versionMinor[1] = $versionMinor[1] ?? '';
        if ($versionMajor[1] && $versionMinor[1]) {
            return $versionMajor[1] . '.' . $versionMinor[1];
        }

        return 'unknown';
    }

    /**
     * @return string[] Returns a array auf templates directory names (not paths).
     */
    public static function getTemplates(): array
    {
        $templates = [];

        $path = App::getShopRoot() . '/templates/';
        if (!file_exists($path)) {
            return $templates;
        }

        $files = FileHelper::scanDir($path, FileHelper::DIRS_ONLY, false);
        foreach ($files as $file) {
            $templates[] = basename($file);
        }

        return $templates;
    }

    public static function getAdminDir(): string
    {
        return Config::getAdminDir() ?? self::scanForAdminDir();
    }

    /**
     * This method scans for the shop-admin-directory.
     *
     * @return string Returns the name of the directory of the shop-admin-directory.
     */
    public static function scanForAdminDir(): string
    {
        $adminDirScanner = new AdminDirScanner();
        $adminDirPaths = $adminDirScanner->getAll(App::getShopRoot());

        if (count($adminDirPaths) <= 0) {
            // NOTE: Vielleicht neue class InvalidAdminDirectoryException hinzufügen
            throw new Exception(
                "No valid admin directory found in " . App::getShopRoot()
                . ". A valid admin directory must be named 'admin' or start with 'admin_'."
                . " It should also contain a valid 'check_update.php' file."
                . " If you have a different named admin directory, please refer to"
                . " 'https://module-loader.de/docs/config_config.php#adminDir' for more information."
            );
        }

        if (count($adminDirPaths) >= 2) {
            // NOTE: Vielleicht neue class InvalidAdminDirectoryException hinzufügen
            throw new Exception("More than one valid admin directory found in " . App::getShopRoot());
        }

        return basename($adminDirPaths[0]);
    }
}
