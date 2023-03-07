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
use RobinTheHood\ModifiedModuleLoaderClient\ModuleSorter;

class DependencyBuilder
{
    public function test()
    {
        $moduleLoader = ModuleLoader::getModuleLoader();
        $module = $moduleLoader->loadLatestVersionByArchiveName('firstweb/multi-order');

        $this->satisfiesContraints(
            $module,
            [
                //"composer/autoload" => ['1.2.0'],
                "robinthehood/modified-std-module" => ['0.6.0'],
                //"robinthehood/modified-orm" => ['1.7.0']
                "robinthehood/pdf-bill" => ['0.10.0']
            ]
        );
    }

    public function satisfiesContraints($module, $contraints)
    {
        $moduleTreeNodes = $this->buildModuleTreeByConstraints($module);
        file_put_contents(__DIR__ . '/debug-log-tree-constraint-obj.txt', print_r($moduleTreeNodes, true));
        file_put_contents(__DIR__ . '/debug-log-tree-constraint-obj.json', json_encode($moduleTreeNodes, JSON_PRETTY_PRINT));

        $moduleFlatEntries = [];
        $this->flattenModuleTreeNodes($moduleTreeNodes, $moduleFlatEntries);
        file_put_contents(__DIR__ . '/debug-log-flat-obj.txt', print_r($moduleFlatEntries, true));
        file_put_contents(__DIR__ . '/debug-log-flat-obj.json', json_encode($moduleFlatEntries, JSON_PRETTY_PRINT));

        $moduleFlatEntries = $this->removeModuleFlatEnties($moduleFlatEntries, $contraints);
        file_put_contents(__DIR__ . '/debug-log-flat-filtered-obj.txt', print_r($moduleFlatEntries, true));
        file_put_contents(__DIR__ . '/debug-log-flat-filtered-obj.json', json_encode($moduleFlatEntries, JSON_PRETTY_PRINT));

        $combinations = [];
        $moduleFlatEntries = array_values($moduleFlatEntries);
        $this->buildAllCombinations($moduleFlatEntries, $combinations);
        file_put_contents(__DIR__ . '/debug-log-combinations-obj.txt', print_r($combinations, true));
        file_put_contents(__DIR__ . '/debug-log-combinations-obj.json', json_encode($combinations, JSON_PRETTY_PRINT));

        $this->satisfiesCominations($moduleTreeNodes, $combinations);
    }

    /**
     * @param Module $Module
     * @param int $depth
     * @return ModuleTreeNode[]
     */
    private function buildModuleTreeByConstraints(Module $module, int $depth = 0): array
    {
        if ($depth >= 10) {
            return [];
        }

        $require = $module->getRequire();

        $moduleTreeNodes = [];
        foreach ($require as $archiveName => $versionConstraint) {
            // Modules to Entry
            $moduleTreeNode = new ModuleTreeNode();
            $moduleTreeNode->archiveName = $archiveName;
            $moduleTreeNode->versionConstraint = $versionConstraint;

            // Fetch Versions
            $moduleLoader = ModuleLoader::getModuleLoader();
            $modules = $moduleLoader->loadAllByArchiveNameAndConstraint($archiveName, $versionConstraint);
            $modules = ModuleSorter::sortByVersion($modules);

            // VersionList
            foreach ($modules as $module) {
                $moduleVersion = new ModuleVersion();
                $moduleVersion->version = $module->getVersion();
                $moduleVersion->require = $this->buildModuleTreeByConstraints($module, $depth + 1);
                $moduleTreeNode->moduleVersions[$moduleVersion->version] = $moduleVersion;
            }

            $moduleTreeNodes[] = $moduleTreeNode;
        }

        return $moduleTreeNodes;
    }

    /**
     * @param ModuleTreeNode[] $moduleTreeNodes
     * @param ModuleFlatEntry[] $moduleFlatEntries
     */
    private function flattenModuleTreeNodes(array $moduleTreeNodes, array &$moduleFlatEntries): void
    {
        if (!$moduleTreeNodes) {
            return;
        }

        foreach ($moduleTreeNodes as $moduleTreeNode) {
            $moduleFlatEntry = new ModuleFlatEntry();
            $moduleFlatEntry->archiveName = $moduleTreeNode->archiveName;
            foreach ($moduleTreeNode->moduleVersions as $moduleVersion) {
                $moduleFlatEntry->versions[] = $moduleVersion->version;
                $this->flattenModuleTreeNodes($moduleVersion->require, $moduleFlatEntries);
            }
            $moduleFlatEntries[$moduleTreeNode->archiveName] = $moduleFlatEntry;
        }
    }

    private function removeModuleFlatEnties(array $moduleFlatTreeEntries, $contraints): array
    {
        foreach ($contraints as $archiveName => $versions) {
            $moduleFlatTreeEntries = $this->removeModuleFlatEnty($moduleFlatTreeEntries, $archiveName, $versions);
        }
        return $moduleFlatTreeEntries;
    }

    private function removeModuleFlatEnty(array $moduleFlatTreeEntries, string $archiveName, array $versions): array
    {
        $filteredModuleFlatTreeEntries = [];
        foreach ($moduleFlatTreeEntries as $moduleFlatTreeEntry) {
            if ($moduleFlatTreeEntry->archiveName !== $archiveName) {
                $filteredModuleFlatTreeEntries[$moduleFlatTreeEntry->archiveName] = $moduleFlatTreeEntry;
                continue;
            }

            $fileredVersions = [];
            foreach ($moduleFlatTreeEntry->versions as $versionStr) {
                if (!in_array($versionStr, $versions)) {
                    continue;
                }
                $fileredVersions[] = $versionStr;
            }
            $newModuleFlatTreeEntry = new ModuleFlatEntry();
            $newModuleFlatTreeEntry->archiveName = $moduleFlatTreeEntry->archiveName;
            $newModuleFlatTreeEntry->versions = $fileredVersions;
            $filteredModuleFlatTreeEntries[$moduleFlatTreeEntry->archiveName] = $newModuleFlatTreeEntry;
        }
        return $filteredModuleFlatTreeEntries;
    }

    /**
     * @param ModuleFlatEntry[] $moduleFlatEntries
     * @param array $moduleFlatEntries
     * @param int $index
     * @param string[] $versionList
     */
    private function buildAllCombinations(array &$moduleFlatEntries, array &$combinations, int $index = 0, array $versionList = [])
    {
        /** @var ModuleFlatEntry*/
        $moduleFlatEntry = $moduleFlatEntries[$index] ?? [];

        if (!$moduleFlatEntry) {
            $combinations[] = $versionList;
            return;
        }

        foreach ($moduleFlatEntry->versions as $versionStr) {
            $version = [
                $moduleFlatEntry->archiveName => $versionStr
            ];
            $newVersionList = array_merge($versionList, $version);
            $this->buildAllCombinations($moduleFlatEntries, $combinations, $index + 1, $newVersionList);
        }
    }

    private function satisfiesCominations(array $moduleTreeNodes, array $combinations)
    {
        foreach ($combinations as $combination) {
            $combination['robinthehood/modified-orm'] = '1.7.0';
            $result = $this->satisfiesComination($moduleTreeNodes, $combination);
            if ($result) {
                var_dump($combination);
                var_dump($result);
                break;
            }
        }
    }

    private function satisfiesComination(array $moduleTreeNodes, array $combination): bool
    {
        // Context: Expanded
        $moduleResult = true;
        foreach ($moduleTreeNodes as $moduleTreeNode) {
            // Context: Module
            $archiveName = $moduleTreeNode->archiveName;
            $selectedVersion = $combination[$archiveName];
            $versionResult = false;
            foreach ($moduleTreeNode->moduleVersions as $moduleVersion) {
                // Context: Version
                if ($moduleVersion->version === $selectedVersion) {
                    $versionResult = $this->satisfiesComination($moduleVersion->require, $combination);
                    break;
                }
            }

            $moduleResult = $moduleResult && $versionResult;
        }
        return $moduleResult;
    }
}
