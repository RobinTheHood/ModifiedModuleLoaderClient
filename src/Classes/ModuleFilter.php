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
use RobinTheHood\ModifiedModuleLoaderClient\ModuleStatus;

class ModuleFilter
{
    public static function filterLoaded($modules)
    {
        $filteredModules = [];
        foreach($modules as $module) {
            if ($module->isLoaded()) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    public static function filterInstalled($modules)
    {
        $filteredModules = [];
        foreach($modules as $module) {
            if ($module->isInstalled()) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    public static function filterUpdatable(array $modules): array
    {
        $filteredModules = [];
        foreach($modules as $module) {
            if (ModuleStatus::isUpdatable($module)) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    public static function filterRepairable(array $modules): array
    {
        $filteredModules = [];
        foreach($modules as $module) {
            if (ModuleStatus::isRepairable($module)) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    public static function filterNotLoaded($modules)
    {
        $filteredModules = [];
        foreach($modules as $module) {
            if (!$module->isLoaded()) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    public static function filterValid($modules)
    {
        $filteredModules = [];
        foreach($modules as $module) {
            if (ModuleStatus::isValid($module)) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    public static function filterNewestVersion($modules)
    {
        $filteredModules = [];
        foreach($modules as $module) {
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

    public static function filterNewestOrInstalledVersion($modules)
    {
        $filteredModules = [];
        foreach($modules as $module) {
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

    public static function filterByArchiveName($modules, $archiveName)
    {
        $filteredModules = [];
        foreach($modules as $module) {
            if ($module->getArchiveName() == $archiveName) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    public static function filterByVersion($modules, $version)
    {
        $filteredModules = [];
        foreach($modules as $module) {
            if ($module->getVersion() == $version) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    public static function getByArchiveNameAndVersion($modules, $archiveName, $version)
    {
        foreach ($modules as $module) {
            if ($module->getArchiveName() != $archiveName) {
                continue;
            }

            if ($module->getVersion() == $version) {
                return $module;
            }
        }
    }

    public static function filterByVersionConstrain($modules, $constrain)
    {
        $filteredModules = [];
        foreach($modules as $module) {
            $comparator = new Comparator(new Parser());
            if ($comparator->satisfies($module->getVersion(), $constrain)) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    public static function getLatestVersion($modules)
    {
        $selectedModule = null;
        foreach($modules as $module) {
            $comparator = new Comparator(new Parser());
            if (!$selectedModule || $comparator->greaterThan($module->getVersion(), $selectedModule->getVersion())) {
                $selectedModule = $module;
            }
        }
        return $selectedModule;
    }
}
