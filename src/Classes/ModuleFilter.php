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

                //if ($module->getVersion() < $filteredModule->getVersion()) {
                if (Semver::lessThan($module->getVersion(), $filteredModule->getVersion())) {
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

                //if ($module->getVersion() < $filteredModule->getVersion()) {
                if (Semver::lessThan($module->getVersion(), $filteredModule->getVersion())) {
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
            //if ($module->getVersion() == $version) {
            //if (Semver::equalTo($module->getVersion(), $version)) {
            if (Semver::satisfies($module->getVersion(), $constrain)) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    public static function getNewestVersion($modules)
    {
        $selectedModule = null;
        foreach($modules as $module) {
            //if (!$selectedModule || $module->getVersion() > $selectedModule->getVersion()) {
            if (!$selectedModule || Semver::greaterThan($module->getVersion(), $selectedModule->getVersion())) {
                $selectedModule = $module;
            }
        }
        return $selectedModule;
    }

    public static function groupByCategory($modules)
    {
        $groupedModules = [];
        foreach($modules as $module) {
            $category = $module->getCategory();
            $groupedModules[$category][] = $module;
        }

        if (isset($groupedModules[''])) {
            $groupedModules['nocategory'] = $groupedModules[''];
            unset($groupedModules['']);
        }

        if (isset($groupedModules['library'])) {
            $temp = $groupedModules['library'];
            unset($groupedModules['library']);
            $groupedModules['library'] = $temp;
        }
        
        return $groupedModules;
    }

    public static function getCategoryName($category)
    {
        if ($category == 'import/export') {
            return 'Import/Export';

        } elseif ($category == 'persistance') {
            return 'Datenbank Module';

        } elseif ($category == 'productivity') {
            return 'Produktivität';

        } elseif ($category == 'promotion/marketing') {
            return 'Promotion & Marketing';

        } elseif ($category == 'productinfos') {
            return 'Zusatzinformationen & Produkt-Tabs';

        } elseif ($category == 'shipping') {
            return 'Versand Module';

        } elseif ($category == 'library') {
            return 'Programmcode Bibliotheken';

        } elseif ($category == 'nocategory') {
            return 'Sonstige Module';

        } elseif ($category) {
            return $category;
        }

        return 'Sonstige Module';
    }

    public static function orderByArchiveName($modules)
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

    public static function orderByIsInstalled($modules)
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

    public static function orderByCategory($modules)
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

    public static function orderByVersion($modules)
    {
        usort($modules, function($moduleA, $moduleB) {
            //if ($moduleA->getVersion() < $moduleB->getVersion()) {
            if (Semver::lessThan($moduleA->getVersion(), $moduleB->getVersion())) {
                return 1;
            } else {
                return -1;
            }
        });
        return $modules;
    }
}
