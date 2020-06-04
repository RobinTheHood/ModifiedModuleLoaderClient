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

use RobinTheHood\ModifiedModuleLoaderClient\ModuleStatus;
use RobinTheHood\ModifiedModuleLoaderClient\Semver;
use RobinTheHood\ModifiedModuleLoaderClient\SemverParser;

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

                $semver = new Semver(new SemverParser());
                if ($semver->lessThan($module->getVersion(), $filteredModule->getVersion())) {
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

                $semver = new Semver(new SemverParser());
                if ($semver->lessThan($module->getVersion(), $filteredModule->getVersion())) {
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

    public static function filterByVersionConstrain($modules, $constrain)
    {
        $filteredModules = [];
        foreach($modules as $module) {
            $semver = new Semver(new SemverParser());
            if ($semver->satisfies($module->getVersion(), $constrain)) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    public static function getNewestVersion($modules)
    {
        $selectedModule = null;
        foreach($modules as $module) {
            $semver = new Semver(new SemverParser());
            if (!$selectedModule || $semver->greaterThan($module->getVersion(), $selectedModule->getVersion())) {
                $selectedModule = $module;
            }
        }
        return $selectedModule;
    }
}
