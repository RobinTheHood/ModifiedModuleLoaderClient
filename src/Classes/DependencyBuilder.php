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

use RobinTheHood\ModifiedModuleLoaderClient\Loader\ModuleLoader;

class DependencyBuilder
{
    // protected $comparator;

    // public function __construct()
    // {
    //     $this->comparator = new Comparator(new Parser());
    // }

    // public function getInstalledModules()
    // {
    //     $localModuleLoader = LocalModuleLoader::getModuleLoader();
    //     $modules = $localModuleLoader->loadAllVersions();
    //     $installedModules = ModuleFilter::filterInstalled($modules);
    //     return $installedModules;
    // }

    // public function getAllModules($module)
    // {
    //     $requireModulesTree = $this->buildTreeByModule($module);

    //     $requireModules = [];
    //     $this->flattenTree($requireModulesTree, $requireModules);

    //     $requireModules = $this->getUniqueRequireModules($requireModules);

    //     $modules = [];
    //     foreach ($requireModules as $requireModule) {
    //         $modules[] = $requireModule['module'];
    //     }

    //     return $modules;
    // }

    // public function canBeInstalled($module)
    // {
    //     $modules = $this->getAllModules($module);
    //     $modules[] = $module;
    //     foreach ($modules as $module) {
    //         $this->canBeInstalledTestRequiers($module, $modules);
    //         $this->canBeInstalledTestSelected($module, $modules);
    //         $this->canBeInstalledTestInstalled($module);
    //     }
    // }

    // public function canBeInstalledTestInstalled($module)
    // {
    //     $installedModules = $this->getInstalledModules();
    //     $this->canBeInstalledTestSelected($module, $installedModules);
    // }

    // public function canBeInstalledTestSelected($module, $modules)
    // {
    //     $usedByEntrys = $this->getUsedByEntrys($module, $modules);
    //     foreach ($usedByEntrys as $usedByEntry) {
    //         if (!$this->comparator->satisfies($module->getVersion(), $usedByEntry['requiredVersion'])) {
    //             $a = $module->getArchiveName();
    //             $av = $module->getVersion();
    //             $b = $usedByEntry['module']->getArchiveName();
    //             $bv = $usedByEntry['requiredVersion'];
    //             die("Module $a version $av can not be installed because module $b requires version $bv");
    //         }
    //     }
    // }

    // public function canBeInstalledTestRequiers($module, $modules)
    // {
    //     foreach ($module->getRequire() as $archiveName => $version) {
    //         $moduleFound = false;
    //         foreach ($modules as $selectedModule) {
    //             if ($selectedModule->getArchiveName() != $archiveName) {
    //                 continue;
    //             }

    //             $moduleFound = true;
    //             if (!$this->comparator->satisfies($selectedModule->getVersion(), $version)) {
    //                 $a = $selectedModule->getArchiveName();
    //                 $av = $module->getVersion();
    //                 die("Module $a version $av can not be installed because module $archiveName version $version is required");
    //             }
    //         }

    //         if (!$moduleFound) {
    //             die("Module $archiveName version $version can not be installed because module was not found.");
    //         }
    //     }
    // }

    // Liefert alle Module aus $selectedModules die das Modul $module verwenden
    // inkl. die benötigte Versionsnummer.
    // public function getUsedByEntrys($module, $selectedModules)
    // {
    //     $usedByEntrys = [];
    //     foreach ($selectedModules as $selectedModule) {
    //         foreach ($selectedModule->getRequire() as $archiveName => $version) {
    //             if ($archiveName == $module->getArchiveName()) {
    //                 $usedByEntrys[] = [
    //                     'module' => $selectedModule,
    //                     'requiredVersion' => $version
    //                 ];
    //             }
    //         }
    //     }
    //     return $usedByEntrys;
    // }

    public function flattenTree($moduleTree, &$modules = null)
    {
        if (!$moduleTree) {
            return;
        }

        foreach ($moduleTree as $entry) {
            $modules[] = [
                'module' => $entry['module'],
                'requestedVersion' => $entry['requestedVersion'],
                'selectedVersion' => $entry['selectedVersion']
            ];
            $this->flattenTree($entry['require'], $modules);
        }
    }

    // public function getUniqueRequireModules($requireModules)
    // {
    //     $uniqueModules = [];
    //     foreach ($requireModules as $requireModule) {
    //         $index = $requireModule['module']->getArchiveName() . ':' . $requireModule['selectedVersion'];
    //         $uniqueModules[$index] = [
    //             'module' => $requireModule['module'],
    //             'requestedVersion' => $requireModule['requestedVersion'],
    //             'selectedVersion' => $requireModule['selectedVersion']
    //         ];
    //     }

    //     return array_values($uniqueModules);
    // }

    // private function buildTreeByArchiveName($archiveName, $version)
    // {
    //     $moduleLoader = ModuleLO
    //     $module = $this->loadModuleByArchiveName($archiveName, $version);
    //     return $this->buildTreeByModule($module);
    // }

    // private function buildTreeByModule($module)
    // {
    //     $requireModulesTree = $this->buildTreeByModuleRecursive($module);
    //     return $requireModulesTree;
    // }

    public function test()
    {
        $moduleLoader = ModuleLoader::getModuleLoader();
        $module = $moduleLoader->loadLatestVersionByArchiveName('firstweb/multi-order');

        // var_dump($module);
        // die();
        $tree = $this->buildTreeByModuleRecursive($module);

        echo '<pre>';
        print_r($tree);
    }

    private function buildTreeByModuleRecursive(Module $module, int $depth = 0): array
    {
        if ($depth >= 5) {
            return false;
        }

        $require = $module->getRequire();

        $requireModulesTree = [];
        foreach ($require as $archiveName => $versionConstraint) {
            $moduleLoader = ModuleLoader::getModuleLoader();

            // An dieser Stelle wird zurzeit immer die neuste Variante ausgewählt.
            // Eigentlich müssen hier alle Varianten die zu $versionConstraint passen
            // ausgewählt und weiter verarbeitet werden. Zurzeit wird $versionConstraint
            // aber nicht beachtet. Das muss bei einer späteren Version verbessert werden.
            $selectedModule = $moduleLoader->loadLatestVersionByArchiveName($archiveName);

            $entry = [];
            if ($selectedModule) {
                // $entry['module'] = $selectedModule;
                $entry['archiveName'] = $selectedModule->getArchiveName();
                $entry['requestedVersion'] = $versionConstraint;
                $entry['selectedVersion'] = $selectedModule->getVersion();
                $entry['require'] = [];
                $requireModules = $this->buildTreeByModuleRecursive($selectedModule, ++$depth);

                if ($requireModules) {
                    $entry['require'] = $requireModules;
                }

                $requireModulesTree[] = $entry;
            }
        }

        return $requireModulesTree;
    }

    // private function loadLatestVersionByArchiveName(string $archiveName): ?Module
    // {
    //     $modules = [];
    //     $localModuleLoader = LocalModuleLoader::getModuleLoader();
    //     $modules[] = $localModuleLoader->loadLatestVersionByArchiveName($archiveName);

    //     $remoteModuleLoader = RemoteModuleLoader::getModuleLoader();
    //     $modules[] = $remoteModuleLoader->loadLatestVersionByArchiveName($archiveName);

    //     $latestVersion = ModuleFilter::getLatestVersion($modules);
    //     return $latestVersion;
    // }
}
