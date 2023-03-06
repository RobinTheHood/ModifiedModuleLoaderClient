<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient\DependencyManager;

use RobinTheHood\ModifiedModuleLoaderClient\Loader\ModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Module;

class DependencyBuilder
{
    public function test()
    {
        $moduleLoader = ModuleLoader::getModuleLoader();
        $module = $moduleLoader->loadLatestVersionByArchiveName('firstweb/multi-order');


        // $modules = [];
        // $this->getModuleList($modules, $module);
        // file_put_contents(__DIR__ . '/debug-log-modules.txt', print_r($modules, true));
        // file_put_contents(__DIR__ . '/debug-log-modules.json', json_encode($modules, JSON_PRETTY_PRINT));
        // return;

        // $tree = $this->buildTreeByModuleRecursive($module);
        // file_put_contents(__DIR__ . '/debug-log-tree.txt', print_r($tree, true));
        // file_put_contents(__DIR__ . '/debug-log-tree.json', json_encode($tree, JSON_PRETTY_PRINT));

        $tree = $this->buildTreeByModuleRecursiveConstraint($module);
        file_put_contents(__DIR__ . '/debug-log-tree-constraint.txt', print_r($tree, true));
        file_put_contents(__DIR__ . '/debug-log-tree-constraint.json', json_encode($tree, JSON_PRETTY_PRINT));

        $flatt = [];
        $this->flattenTreeNew($tree, $flatt);
        file_put_contents(__DIR__ . '/debug-log-flatt.txt', print_r($flatt, true));
        file_put_contents(__DIR__ . '/debug-log-flatt.json', json_encode($flatt, JSON_PRETTY_PRINT));

        $combinations = [];
        $this->allCombinations($flatt, $combinations, 0);
        file_put_contents(__DIR__ . '/debug-log-combinations.txt', print_r($combinations, true));
        file_put_contents(__DIR__ . '/debug-log-combinations.json', json_encode($combinations, JSON_PRETTY_PRINT));

        $combinations = array_reverse($combinations);
        foreach ($combinations as $combination) {
            $combination['composer/autoload'] = '1.0.0';
            $result = $this->satisfiesComination($tree, $combination);
            if ($result) {
                var_dump($combination);
                var_dump($result);
                break;
            }
        }
    }


    private function allCombinations(&$flatt, &$combinations, int $index, $combination = [])
    {
        $entry = array_values($flatt)[$index] ?? [];

        if (!$entry) {
            $combinations[] = $combination;
            return;
        }

        foreach ($entry['versions'] as $version) {
            $newCombination = array_merge($combination, [$entry['archiveName'] => $version]);
            $this->allCombinations($flatt, $combinations, $index + 1, $newCombination);
        }
    }

    private function satisfiesComination(&$tree, $combination): bool
    {
        // Expanded
        $moduleResult = true;
        foreach ($tree as &$module) {
            // Module
            $archiveName = $module['archiveName'];
            $moduleVersions = &$module['versions'];
            $selectedVersion = $combination[$archiveName];
            $versionResult = false;
            foreach ($moduleVersions as $version => &$moduleVersion) {
                // Version
                if ($version === $selectedVersion) {
                    $subTree = &$moduleVersion['requireExpanded'];
                    $versionResult = $this->satisfiesComination($subTree, $combination);
                    break;
                }
            }
            $moduleResult = $moduleResult && $versionResult;
        }
        return $moduleResult;
    }

    private function flattenTreeNew($tree, &$flatt)
    {
        if (!$tree) {
            return;
        }

        foreach ($tree as $entry) {
            $moduleEntry = [];
            $moduleEntry["archiveName"] = $entry['archiveName'];
            foreach ($entry['versions'] as $version => $entrys) {
                $moduleEntry["versions"][] = $version;
                $this->flattenTreeNew($entrys['requireExpanded'], $flatt);
            }
            $flatt[$entry['archiveName']] = $moduleEntry;
        }
    }

    private function getModuleList(array &$modules, Module $module, int $depth = 0)
    {
        if ($depth >= 10) {
            return;
        }

        $requireExpanded = [];
        $require = $module->getRequire();
        foreach ($require as $archiveName => $versionConstraint) {
            $moduleLoader = ModuleLoader::getModuleLoader();
            $moduleVersions = $moduleLoader->loadAllByArchiveNameAndConstraint($archiveName, $versionConstraint);
            $versions = [];
            foreach ($moduleVersions as $moduleA) {
                $versions[] = $archiveName . ' : ' . $moduleA->getVersion();
            }
            $requireExpanded[$archiveName] = $versions;
        }

        $moduleAsArray = [
            'archiveName' => $module->getArchiveName(),
            'version' => $module->getVersion(),
            'require' => $require,
            'requireExpanded' => $requireExpanded
        ];

        if (!$this->containsModule($moduleAsArray, $modules)) {
            $modules[] = $moduleAsArray;
        }

        $require = $module->getRequire();
        foreach ($require as $archiveName => $versionConstraint) {
            $moduleLoader = ModuleLoader::getModuleLoader();
            $moduleVersions = $moduleLoader->loadAllByArchiveNameAndConstraint($archiveName, $versionConstraint);
            // var_dump($archiveName);
            // if ($archiveName == 'robinthehood/modified-std-module') {
            //     echo 'aaa';
            //     var_dump($moduleVersions);
            //     die();
            // }
            foreach ($moduleVersions as $moduleA) {
                $this->getModuleList($modules, $moduleA, $depth + 1);
            }
        }
    }

    private function containsModule($moduleA, $modules)
    {
        foreach ($modules as $moduleB) {
            if ($moduleA['archiveName'] !== $moduleB['archiveName']) {
                continue;
            }

            if ($moduleA['version'] !== $moduleB['version']) {
                continue;
            }

            return true;
        }
        return false;
    }

    private function buildTreeByModuleRecursiveConstraint(Module $module, int $depth = 0): array
    {
        if ($depth >= 10) {
            return [];
        }

        $require = $module->getRequire();

        $requireModulesTree = [];
        foreach ($require as $archiveName => $versionConstraint) {
            // Modules to Entry
            $entry = [];
            $entry['archiveName'] = $archiveName;
            $entry['versionConstraint'] = $versionConstraint;

            // Versions
            $moduleLoader = ModuleLoader::getModuleLoader();
            $modules = $moduleLoader->loadAllByArchiveNameAndConstraint($archiveName, $versionConstraint);
            foreach ($modules as $module) {
                $entry['versions'][$module->getVersion()] = [
                    'value' => false,
                    'requireExpanded' => $this->buildTreeByModuleRecursiveConstraint($module, $depth + 1)
                ];
            }


            $requireModulesTree[] = $entry;
        }

        return $requireModulesTree;
    }

    private function buildTreeByModuleRecursive(Module $module, int $depth = 0): array
    {
        if ($depth >= 10) {
            return [];
        }

        $require = $module->getRequire();

        $requireModulesTree = [];
        foreach ($require as $archiveName => $versionConstraint) {
            // Modules to Entry
            $entry = [];
            $entry['archiveName'] = $archiveName;
            $entry['versionConstraint'] = $versionConstraint;

            // Versions
            $moduleLoader = ModuleLoader::getModuleLoader();
            $modules = $moduleLoader->loadAllVersionsByArchiveName($archiveName);
            foreach ($modules as $module) {
                $entry['versions'][$module->getVersion()] = $this->buildTreeByModuleRecursive($module, $depth + 1);
            }


            $requireModulesTree[] = $entry;
        }

        return $requireModulesTree;
    }


    private function buildTreeByModuleRecursiveOld(Module $module, int $depth = 0): array
    {
        if ($depth >= 5) {
            return [];
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
}
