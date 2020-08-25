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

class ModulePathMapper
{
    public static function mmlcToShop(string $mmlcPath): string
    {
        global $configuration;
        $shopPath = preg_replace('/^\/admin\//', '/' . $configuration['adminDir'] . '/', $mmlcPath);
        return $shopPath;
    }

    public static function shopToMmlc(string $shopPath): string
    {
        global $configuration;
        $mmlcPath = preg_replace('/^\/' . $configuration['adminDir'] . '\//', '/admin/', $shopPath);
        return $mmlcPath;
    }

    public static function mmlcPathsToShopPaths(array $mmlcPaths): array
    {
        global $configuration;
        $shopPaths = [];
        foreach ($mmlcPaths as $path) {
            $shopPaths[] = self::mmlcToShop($path);
        }
        return $shopPaths;
    }

    public static function shopPathsToMmlcPaths(array $shopPaths): array
    {
        global $configuration;
        $mmlcPaths = [];
        foreach ($shopPaths as $path) {
            $mmlcPaths[] = self::mmlcToShop($path);
        }
        return $mmlcPaths;
    }
}