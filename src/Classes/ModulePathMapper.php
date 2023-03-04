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

use RobinTheHood\ModifiedModuleLoaderClient\ShopInfo;

class ModulePathMapper
{
    private const DEFAULT_ADMIN_DIR = 'admin';
    private const DEFAULT_SHOP_VENDOR_MMLC_DIR = 'vendor-mmlc';

    /**
     * FromBase: Modules/<VENDOR-NAME><MODULE-NAME>/src
     * From: /...
     * ToBase: <SHOP-ROOT>
     * To: /...
     *
     * Its converts the name of the admin dir too.
     */
    public static function moduleSrcToShopRoot(string $mmlcPath): string
    {
        $adminDir = ShopInfo::getAdminDir();
        // Replace string that starts with "/DEFAULT_ADMIN_DIR/"
        $shopPath = preg_replace('/^\/' . self::DEFAULT_ADMIN_DIR . '\//', '/' . $adminDir . '/', $mmlcPath);
        return $shopPath;
    }

    /**
     * FromBase: <SHOP-ROOT>
     * From: /...
     * ToBase: Modules/<VENDOR-NAME><MODULE-NAME>/src
     * To: /...
     *
     * Its converts the name of the admin dir too.
     */
    public static function shopRootToModuleSrc(string $shopPath): string
    {
        $adminDir = ShopInfo::getAdminDir();
        // Replace string that starts with "/$adminDir/"
        $mmlcPath = preg_replace('/^\/' . $adminDir . '\//', '/' . self::DEFAULT_ADMIN_DIR . '/', $shopPath);
        return $mmlcPath;
    }

    /**
     * FromBase: Modules/<VENDOR-NAME><MODULE-NAME>/src-mmlc
     * From: /...
     * ToBase: <SHOP-ROOT>
     * To: /vendor-mmlc/<VENDOR-NAME><MODULE-NAME>/...
     */
    public static function moduleSrcMmlcToShopVendorMmlc(string $path, string $archiveName): string
    {
        return '/' . self::DEFAULT_SHOP_VENDOR_MMLC_DIR . '/' . $archiveName . '/' . $path;
    }

    /**
     * FromBase: <SHOP-ROOT>
     * From: /vendor-mmlc/<VENDOR-NAME><MODULE-NAME>/...
     * ToBase: Modules/<VENDOR-NAME><MODULE-NAME>/src-mmlc
     * To: /...
     */
    public static function shopVendorMmlcToModuleSrcMmlc(string $vendorMmlcPath, string $archiveName): string
    {
        $string = '/' . self::DEFAULT_SHOP_VENDOR_MMLC_DIR . '/' . $archiveName . '/';
        $replace = str_replace('/', '\/', $string);
        $srcMmlcPath = preg_replace('/^' . $replace . '/', '/', $vendorMmlcPath);
        return $srcMmlcPath;
    }

    /**
     * Map multible
     *
     * FromBase: Modules/<VENDOR-NAME><MODULE-NAME>/src-mmlc
     * From: /...
     * ToBase: <SHOP-ROOT>
     * To: /vendor-mmlc/<VENDOR-NAME><MODULE-NAME>/...
     */
    public static function allModuleSrcMmlcToShopVendorMmlc(array $paths, string $archiveName): array
    {
        $resultPaths = [];
        foreach ($paths as $path) {
            $resultPaths[] = self::moduleSrcMmlcToShopVendorMmlc($path, $archiveName);
        }
        return $resultPaths;
    }

    /**
     * Converts multible module src paths to shop root . For example this method
     * renames all custome admin-directory-names like admin to admin_123456.
     *
     * FromBase: Modules/<VENDOR-NAME><MODULE-NAME>/src
     * From: /...
     * ToBase: <SHOP-ROOT>
     * To: /...
     *
     * @param string[] $shopPaths A Array of path in shop-path-scope
     * @return string[] Returns a array of mapped strings
     */
    public static function allModuleSrcToShopRoot(array $mmlcPaths): array
    {
        $shopPaths = [];
        foreach ($mmlcPaths as $path) {
            $shopPaths[] = self::moduleSrcToShopRoot($path);
        }
        return $shopPaths;
    }

    /**
     * Converts multible shop root paths to module src paths. For example this method
     * renames all custome admin-directory-names like admin_123456 to admin.
     *
     * FromBase: <SHOP-ROOT>
     * From: /...
     * ToBase: Modules/<VENDOR-NAME><MODULE-NAME>/src
     * To: /...
     *
     * @param string[] $shopPaths A Array of path in shop-path-scope
     * @return string[] Returns a array of mapped strings
     */
    public static function allShopRootToModuleSrc(array $shopPaths): array
    {
        $mmlcPaths = [];
        foreach ($shopPaths as $path) {
            $mmlcPaths[] = self::moduleSrcToShopRoot($path);
        }
        return $mmlcPaths;
    }
}
