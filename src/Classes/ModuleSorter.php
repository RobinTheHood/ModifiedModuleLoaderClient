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

use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;

class ModuleSorter
{
    public static function sortByArchiveName($modules)
    {
        usort($modules, function($moduleA, $moduleB) {
            if ($moduleA->getArchiveName() < $moduleB->getArchiveName()) {
                return -1;
            } else {
                return 1;
            }
        });
        return $modules;
    }

    public static function sortByIsInstalled($modules)
    {
        usort($modules, function($moduleA, $moduleB) {
            if ($moduleA->isInstalled()) {
                return -1;
            } else {
                return 1;
            }
        });
        return $modules;
    }

    public static function sortByCategory($modules)
    {
        usort($modules, function($moduleA, $moduleB) {

            if ($moduleA->getCategory() < $moduleB->getCategory()) {
                return 1;
            } else {
                return -1;
            }
        });
        return $modules;
    }

    public static function sortByVersion($modules)
    {
        usort($modules, function($moduleA, $moduleB) {
            $comparator = new Comparator(new Parser());
            if ($comparator->lessThan($moduleA->getVersion(), $moduleB->getVersion())) {
                return 1;
            } else {
                return -1;
            }
        });
        return $modules;
    }
}