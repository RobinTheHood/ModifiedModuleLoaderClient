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
    public static function getModifiedVersion()
    {
        $path = App::getShopRoot() . '/admin/includes/version.php';
        if (!file_exists($path)) {
            return false;
        }

        $fileStr = file_get_contents($path);
        $pos = strpos($fileStr, 'MOD_');
        $version = substr($fileStr, (int) $pos + 4, 7);
        return $version;
    }

    public static function getTemplates()
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
}
