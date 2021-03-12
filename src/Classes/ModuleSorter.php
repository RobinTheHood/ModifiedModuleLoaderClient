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

use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;

class ModuleSorter
{
    /**
     * @param Module[] $modules
     * @return Module[] Return a array of modules sorted by ArchiveName.
     */
    public static function sortByArchiveName(array $modules): array
    {
        usort($modules, function (Module $moduleA, Module $moduleB): int {
            if ($moduleA->getArchiveName() < $moduleB->getArchiveName()) {
                return -1;
            } else {
                return 1;
            }
        });
        return $modules;
    }

    /**
     * @param Module[] $modules
     * @return Module[] Return a array of modules sorted by isInstalled.
     */
    public static function sortByIsInstalled($modules): array
    {
        usort($modules, function (Module $moduleA, Module $moduleB): int {
            if ($moduleA->isInstalled()) {
                return -1;
            } else {
                return 1;
            }
        });
        return $modules;
    }

    /**
     * @param Module[] $modules
     * @return Module[] Return a array of modules sorted by category.
     */
    public static function sortByCategory($modules): array
    {
        usort($modules, function (Module $moduleA, Module $moduleB): int {
            if ($moduleA->getCategory() < $moduleB->getCategory()) {
                return 1;
            } else {
                return -1;
            }
        });
        return $modules;
    }

    /**
     * @param Module[] $modules
     * @return Module[] Return a array of modules sorted by version.
     */
    public static function sortByVersion(array $modules): array
    {
        usort($modules, function (Module $moduleA, Module $moduleB): int {
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
