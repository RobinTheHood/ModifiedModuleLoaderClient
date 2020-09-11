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
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\DemoMode;

class LinkBuilder
{
    public static function getModulUrlPretty(Module $module): string
    {
        return App::getUrlRoot() . '/' . $module->getArchiveName() . '/' . $module->getVersion();
    }

    public static function getModulUrlQuery(Module $module): string
    {
        return App::getUrlRoot() . '?action=moduleInfo&archiveName=' . $module->getArchiveName() . '&version=' . $module->getVersion();
    }

    public static function getModulUrl(Module $module): string
    {
        if (DemoMode::isDemo()) {
            return self::getModulUrlPretty($module);
        }

        return self::getModulUrlQuery($module);
    }


    public static function getModulUrlByValuePretty(string $archiveName, string $version = ''): string
    {
        if ($version) {
            return App::getUrlRoot() . '/' . $archiveName . '/' . $version;
        }
        return App::getUrlRoot() . '/' . $archiveName;
    }

    public static function getModulUrlByValueQuery(string $archiveName, string $version = ''): string
    {
        if ($version) {
            return App::getUrlRoot() . '?action=moduleInfo&archiveName=' . $archiveName . '&version=' . $version;
        }
        return App::getUrlRoot() . '?action=moduleInfo&archiveName=' . $archiveName;
    }

    public static function getModulUrlByValue(string $archiveName, string $version = ''): string
    {
        if (DemoMode::isDemo()) {
            return self::getModulUrlByValuePretty($archiveName, $version);
        }

        return self::getModulUrlByValueQuery($archiveName, $version);
    }
}
