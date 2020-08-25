<?php

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
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;

class ShopInfo
{

    /**
     * @return string Returns the installed modified version as string.
     */
    public static function getModifiedVersion(): string
    {
        $path = App::getShopRoot() . '/admin/includes/version.php';
        if (!file_exists($path)) {
            return '';
        }

        $fileStr = file_get_contents($path);
        $pos = strpos($fileStr, 'MOD_');
        $version = substr($fileStr, (int) $pos + 4, 7);
        return $version;
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
        foreach($files as $file) {
            $templates[] = basename($file);
        }

        return $templates;
    }

    public static function getAdminDir(): string
    {
        global $configuration;
        return $configuration['adminDir'] ?? self::scanForAdminDir();
    }

    /**
     * This method scans for the shop-admin-directory.
     * 
     * @return string Returns the name of the directory of the shop-admin-directory.
     */
    public static function scanForAdminDir(): string
    {
        $resultDirectory = 'admin'; // Set default

        $knownAdminPath = App::getShopRoot() . '/admin';

        if (\file_exists($knownAdminPath) && \is_dir($knownAdminPath)) {
            return $resultDirectory;
        }

        $directorys = FileHelper::scanDir(App::getShopRoot(), FileHelper::DIRS_ONLY, false);

        // List of Files, that the admin dire
        $files = [
            'check_update.php'
        ];

        foreach ($directorys as $directory) {
            if (!FileHelper::containsAllFiles($files, $directory)) {
                continue;
            }

            $resultDirectory = $directory;
            break;
        }

        return basename($resultDirectory);
    }
}
