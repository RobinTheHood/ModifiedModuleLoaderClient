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

class App
{
    protected static $modulesDir = 'Modules';
    protected static $archivesDir = 'Archives';
    protected static $configDir = 'config';

    public static function setModulesDir($modulesDir)
    {
        self::$modulesDir = $modulesDir;
    }

    public static function setArchivesDir($archivesDir)
    {
        self::$archivesDir = $archivesDir;
    }

    public static function getRoot()
    {
        return realPath(__DIR__ . '/../../');
    }

    public static function getShopRoot()
    {
        return realPath(__DIR__ . '/../../../');
    }

    public static function getSrcRoot()
    {
        return self::getRoot() . '/src';
    }

    public static function getTemplatesRoot()
    {
        return self::getRoot() . '/src/Templates';
    }

    public static function getConfigRoot()
    {
        return self::getRoot() . '/' . self::$configDir;
    }

    public static function getArchivesRoot()
    {
        return self::getRoot() . '/' . self::$archivesDir;
    }

    public static function getModulesRoot()
    {
        return self::getRoot() . '/' . self::getModulesDirName();
    }

    public static function getModulesDirName()
    {
        return self::$modulesDir;
    }

    // (www.shop.de) /shop/ModifiedModuleLoaderClient
    public static function getUrlRoot(): string
    {
        return dirname($_SERVER['PHP_SELF']);
    }

    // (www.shop.de) /shop
    public static function getUrlShopRoot(): string
    {
        return dirname(self::getUrlRoot());
    }
}
