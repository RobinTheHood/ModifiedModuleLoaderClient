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
    private const DEFAULT_ADMIN_DIR = 'admin';

    public static function mmlcToShop(string $mmlcPath): string
    {
        $adminDir = ShopInfo::getAdminDir();
        // Replace string that starts with "/DEFAULT_ADMIN_DIR/"
        $shopPath = preg_replace('/^\/' . self::DEFAULT_ADMIN_DIR . '\//', '/' . $adminDir . '/', $mmlcPath);
        return $shopPath;
    }

    public static function shopToMmlc(string $shopPath): string
    {
        $adminDir = ShopInfo::getAdminDir();
        // Replace string that starts with "/$adminDir/"
        $mmlcPath = preg_replace('/^\/' . $adminDir . '\//', '/' . self::DEFAULT_ADMIN_DIR . '/', $shopPath);
        return $mmlcPath;
    }

    /**
     * Converts multible mmlc-paths to shop-paths. For example this method
     * renames all custome admin-directory-names like admin to admin_123456.
     *
     * @param string[] $shopPaths A Array of path in shop-path-scope
     * @return string[] Returns a array of mapped strings
     */
    public static function mmlcPathsToShopPaths(array $mmlcPaths): array
    {
        $shopPaths = [];
        foreach ($mmlcPaths as $path) {
            $shopPaths[] = self::mmlcToShop($path);
        }
        return $shopPaths;
    }

    /**
     * Converts multible shop-paths to mmlc-paths. For example this method
     * renames all custome admin-directory-names like admin_123456 to admin.
     *
     * @param string[] $shopPaths A Array of path in shop-path-scope
     * @return string[] Returns a array of mapped strings
     */
    public static function shopPathsToMmlcPaths(array $shopPaths): array
    {
        $mmlcPaths = [];
        foreach ($shopPaths as $path) {
            $mmlcPaths[] = self::mmlcToShop($path);
        }
        return $mmlcPaths;
    }
}
