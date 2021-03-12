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

use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleStatus;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;

class ModuleFilter
{
    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public static function filterLoaded(array $modules): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            if ($module->isLoaded()) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public static function filterInstalled(array $modules): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            if ($module->isInstalled()) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public static function filterUpdatable(array $modules): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            if (ModuleStatus::isUpdatable($module)) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public static function filterRepairable(array $modules): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            if (ModuleStatus::isRepairable($module)) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public static function filterNotLoaded(array $modules): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            if (!$module->isLoaded()) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public static function filterValid(array $modules): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            if (ModuleStatus::isValid($module)) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public static function filterNewestVersion(array $modules): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            $insertOrReplace = true;
            foreach ($filteredModules as $filteredModule) {
                if ($module->getArchiveName() != $filteredModule->getArchiveName()) {
                    continue;
                }

                $comparator = new Comparator(new Parser());
                if ($comparator->lessThan($module->getVersion(), $filteredModule->getVersion())) {
                    $insertOrReplace = false;
                    break;
                }
            }

            if ($insertOrReplace) {
                $filteredModules[$module->getArchiveName()] = $module;
            }
        }

        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public static function filterNewestOrInstalledVersion($modules): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            $insertOrReplace = true;
            foreach ($filteredModules as $filteredModule) {
                if ($module->getArchiveName() != $filteredModule->getArchiveName()) {
                    continue;
                }

                if ($filteredModule->isInstalled()) {
                    $insertOrReplace = false;
                    break;
                }

                if ($module->isInstalled()) {
                    break;
                }

                $comparator = new Comparator(new Parser());
                if ($comparator->lessThan($module->getVersion(), $filteredModule->getVersion())) {
                    $insertOrReplace = false;
                    break;
                }
            }

            if ($insertOrReplace) {
                $filteredModules[$module->getArchiveName()] = $module;
            }
        }

        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public static function filterByArchiveName(array $modules, string $archiveName): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            if ($module->getArchiveName() == $archiveName) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public static function filterByVersion(array $modules, string $version): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            if ($module->getVersion() == $version) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public static function filterByVersionConstrain(array $modules, string $constrain): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            $comparator = new Comparator(new Parser());
            if ($comparator->satisfies($module->getVersion(), $constrain)) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     */
    public static function getLatestVersion(array $modules): ?Module
    {
        $selectedModule = null;
        foreach ($modules as $module) {
            $comparator = new Comparator(new Parser());
            if (!$selectedModule || $comparator->greaterThan($module->getVersion(), $selectedModule->getVersion())) {
                $selectedModule = $module;
            }
        }
        return $selectedModule;
    }

        /**
     * @param Module[] $modules
     * @return Module|null
     */
    public static function getByArchiveNameAndVersion(array $modules, string $archiveName, string $version): ?Module
    {
        foreach ($modules as $module) {
            if ($module->getArchiveName() != $archiveName) {
                continue;
            }

            if ($module->getVersion() == $version) {
                return $module;
            }
        }
        return null;
    }
}
