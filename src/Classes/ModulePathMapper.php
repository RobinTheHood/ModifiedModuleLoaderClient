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

use RobinTheHood\ModifiedModuleLoaderClient\Helpers\Hasher;
use RobinTheHood\ModifiedModuleLoaderClient\ShopInfo;

class ModulePathMapper
{
    const DEFAULT_ADMIN_DIR = 'admin';

    public static function mmlcToShop(string $mmlcPath): string
    {
        $adminDir = ShopInfo::getAdminDir();
        // Replace string that starts with "/DEFAULT_ADMIN_DIR/"
        $shopPath = preg_replace('/^\/' . DEFAULT_ADMIN_DIR .'\//', '/' . $adminDir . '/', $mmlcPath);
        return $shopPath;
    }

    public static function shopToMmlc(string $shopPath): string
    {
        $adminDir = ShopInfo::getAdminDir();
        // Replace string that starts with "/$adminDir/"
        $mmlcPath = preg_replace('/^\/' . $adminDir . '\//', '/' . DEFAULT_ADMIN_DIR . '/', $shopPath);
        return $mmlcPath;
    }

    public static function mmlcPathsToShopPaths(array $mmlcPaths): array
    {
        $shopPaths = [];
        foreach ($mmlcPaths as $path) {
            $shopPaths[] = self::mmlcToShop($path);
        }
        return $shopPaths;
    }

    public static function shopPathsToMmlcPaths(array $shopPaths): array
    {
        $mmlcPaths = [];
        foreach ($shopPaths as $path) {
            $mmlcPaths[] = self::mmlcToShop($path);
        }
        return $mmlcPaths;
    }
}