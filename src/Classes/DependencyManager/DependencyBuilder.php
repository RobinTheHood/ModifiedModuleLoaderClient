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
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFilter;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleSorter;

class DependencyBuilder
{
    /** @var Module[] */
    private $moduleCache = [];

    public function test()
    {
        $moduleLoader = ModuleLoader::getModuleLoader();
        $module = $moduleLoader->loadLatestVersionByArchiveName('firstweb/multi-order');

        $constraints = [
            //"composer/autoload" => ['1.2.0'],
            //"robinthehood/modified-std-module" => ['0.1.0'],
            //"robinthehood/modified-orm" => ['1.7.0']
            //"robinthehood/pdf-bill" => ['0.10.0']
        ];

        var_dump('TEST: satisfiesContraints1');
        $this->satisfiesContraints1(
            $module,
            $constraints
        );

        var_dump('TEST: satisfiesContraints2');
        $this->satisfiesContraints2(
            'firstweb/multi-order',
            '^1.13.0',
            $constraints
        );

        var_dump('TEST: satisfiesContraints3');
        $this->satisfiesContraints3(
            'firstweb/multi-order',
            '^1.0.0',
            $constraints
        );
    }

    public function satisfiesContraints1(Module $module, array $contraints): void
    {
        $moduleTreeNodes = $this->buildModuleTreesByConstraints($module);
        file_put_contents(__DIR__ . '/debug-log-tree-constraint-obj.json', json_encode($moduleTreeNodes, JSON_PRETTY_PRINT));

        $moduleFlatEntries = [];
        $this->buildModuleFlatEntriesByModuleTreeNodes($moduleTreeNodes, $moduleFlatEntries);
        file_put_contents(__DIR__ . '/debug-log-flat-obj.json', json_encode($moduleFlatEntries, JSON_PRETTY_PRINT));

        $moduleFlatEntries = $this->removeModuleFlatEnties($moduleFlatEntries, $contraints);
        file_put_contents(__DIR__ . '/debug-log-flat-filtered-obj.json', json_encode($moduleFlatEntries, JSON_PRETTY_PRINT));

        $combinations = [];
        $moduleFlatEntries = array_values($moduleFlatEntries);
        $this->buildAllCombinationsFromModuleFlatEntries($moduleFlatEntries, $combinations);
        file_put_contents(__DIR__ . '/debug-log-combinations-obj.json', json_encode($combinations, JSON_PRETTY_PRINT));

        $combinationSatisfyer = new CombinationSatisfyer();
        $array = $combinationSatisfyer->satisfiesCominationsFromModuleTreeNodes($moduleTreeNodes, $combinations);
        var_dump($array);
    }


    public function satisfiesContraints2(string $archiveName, string $constraint, array $contraints): void
    {
        $moduleTreeNode = $this->buildModuleTreeByConstraints($archiveName, $constraint);
        file_put_contents(__DIR__ . '/debug-log-tree-constraint-obj-new.json', json_encode($moduleTreeNode, JSON_PRETTY_PRINT));

        $moduleFlatEntries = [];
        $this->buildModuleFlatEntriesByModuleTreeNode($moduleTreeNode, $moduleFlatEntries);
        file_put_contents(__DIR__ . '/debug-log-flat-obj-new.json', json_encode($moduleFlatEntries, JSON_PRETTY_PRINT));

        $moduleFlatEntries = $this->removeModuleFlatEnties($moduleFlatEntries, $contraints);
        file_put_contents(__DIR__ . '/debug-log-flat-filtered-obj.json', json_encode($moduleFlatEntries, JSON_PRETTY_PRINT));

        $combinations = [];
        $moduleFlatEntryList = new ModuleFlatEntryList($moduleFlatEntries);
        $this->buildAllCombinationsFromModuleFlatEntryList($moduleFlatEntryList, $combinations);
        file_put_contents(__DIR__ . '/debug-log-combinations-obj-new.json', json_encode($combinations, JSON_PRETTY_PRINT));

        $combinationSatisfyer = new CombinationSatisfyer();
        $array = $combinationSatisfyer->satisfiesCominationsFromModuleTreeNode($moduleTreeNode, $combinations);
        var_dump($array);
    }


    public function satisfiesContraints3(string $archiveName, string $constraint, array $contraints): void
    {
        $moduleTreeNode = $this->buildModuleTreeByConstraints($archiveName, $constraint);
        file_put_contents(__DIR__ . '/debug-log-tree-constraint-obj-new.json', json_encode($moduleTreeNode, JSON_PRETTY_PRINT));

        $moduleFlatEntries = [];
        $this->buildModuleFlatEntriesByModuleTreeNode($moduleTreeNode, $moduleFlatEntries);
        file_put_contents(__DIR__ . '/debug-log-flat-obj-new.json', json_encode($moduleFlatEntries, JSON_PRETTY_PRINT));

        $moduleFlatEntries = $this->removeModuleFlatEnties($moduleFlatEntries, $contraints);
        file_put_contents(__DIR__ . '/debug-log-flat-filtered-obj.json', json_encode($moduleFlatEntries, JSON_PRETTY_PRINT));

        $combinationIterator = new CombinationIterator($moduleFlatEntries);
        $combinationSatisfyer = new CombinationSatisfyer();
        $array = $combinationSatisfyer->satisfiesCominationsFromModuleWithIterator($moduleTreeNode, $combinationIterator);
        var_dump($array);
    }

    /**
     * @param string $archiveName
     * @param string $versionConstraint
     * @return Module[]
     */
    private function loadAllByArchiveNameAndConstraint(string $archiveName, string $versionConstraint): array
    {
        $modules = $this->moduleCache[$archiveName] ?? [];
        if (!$modules) {
            $moduleLoader = ModuleLoader::getModuleLoader();
            $modules = $moduleLoader->loadAllVersionsByArchiveName($archiveName);
            $modules = ModuleSorter::sortByVersion($modules);
            $this->moduleCache[$archiveName] = $modules;
        }

        return ModuleFilter::filterByVersionConstrain($modules, $versionConstraint);
    }

    /**
     * @param Module $Module
     * @param int $depth
     * @return ModuleTreeNode[]
     */
    private function buildModuleTreesByConstraints(Module $module, int $depth = 0): array
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
            $modules = $this->loadAllByArchiveNameAndConstraint($archiveName, $versionConstraint);

            // VersionList
            foreach ($modules as $module) {
                $moduleVersion = new ModuleVersion();
                $moduleVersion->version = $module->getVersion();
                $moduleVersion->require = $this->buildModuleTreesByConstraints($module, $depth + 1);
                $moduleTreeNode->moduleVersions[$moduleVersion->version] = $moduleVersion;
            }

            $moduleTreeNodes[] = $moduleTreeNode;
        }

        return $moduleTreeNodes;
    }

    /**
     * @param string $archiveName
     * @param string $versionConstraint
     * @param int $depth
     */
    private function buildModuleTreeByConstraints(string $archiveName, string $versionConstraint, int $depth = 0): ModuleTreeNode
    {
        $moduleTreeNode = new ModuleTreeNode();
        $moduleTreeNode->archiveName = $archiveName;
        $moduleTreeNode->versionConstraint = $versionConstraint;

        $modules = $this->loadAllByArchiveNameAndConstraint($archiveName, $versionConstraint);

        $moduleVersions = [];
        foreach ($modules as $module) {
            // Context: Module
            $moduleVersion = new ModuleVersion();
            $moduleVersion->version = $module->getVersion();

            if ($depth < 10) {
                $require = $module->getRequire();
                foreach ($require as $archiveName => $versionConstraint) {
                    // Context: require
                    $moduleVersion->require[] = $this->buildModuleTreeByConstraints($archiveName, $versionConstraint, $depth + 1);
                }
            }
            $moduleVersions[] = $moduleVersion;
        }
        $moduleTreeNode->moduleVersions = $moduleVersions;

        return $moduleTreeNode;
    }

    /**
     * @param ModuleTreeNode[] $moduleTreeNodes
     * @param ModuleFlatEntry[] $moduleFlatEntries
     */
    private function buildModuleFlatEntriesByModuleTreeNodes(array $moduleTreeNodes, array &$moduleFlatEntries): void
    {
        if (!$moduleTreeNodes) {
            return;
        }

        foreach ($moduleTreeNodes as $moduleTreeNode) {
            $moduleFlatEntry = new ModuleFlatEntry();
            $moduleFlatEntry->archiveName = $moduleTreeNode->archiveName;
            foreach ($moduleTreeNode->moduleVersions as $moduleVersion) {
                $moduleFlatEntry->versions[] = $moduleVersion->version;
                $this->buildModuleFlatEntriesByModuleTreeNodes($moduleVersion->require, $moduleFlatEntries);
            }
            $moduleFlatEntries[$moduleTreeNode->archiveName] = $moduleFlatEntry;
        }
    }

    /**
     * @param ModuleTreeNode $moduleTreeNode
     * @param ModuleFlatEntry[] $moduleFlatEntries
     */
    private function buildModuleFlatEntriesByModuleTreeNode(ModuleTreeNode $moduleTreeNode, array &$moduleFlatEntries): void
    {
        $moduleFlatEntry = new ModuleFlatEntry();
        $moduleFlatEntry->archiveName = $moduleTreeNode->archiveName;
        $moduleFlatEntries[$moduleTreeNode->archiveName] = $moduleFlatEntry;
        foreach ($moduleTreeNode->moduleVersions as $moduleVersion) {
            $moduleFlatEntry->versions[] = $moduleVersion->version;
            foreach ($moduleVersion->require as $moduleTreeNode) {
                $this->buildModuleFlatEntriesByModuleTreeNode($moduleTreeNode, $moduleFlatEntries);
            }
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
    private function buildAllCombinationsFromModuleFlatEntries(array &$moduleFlatEntries, array &$combinations, int $index = 0, array $versionList = [])
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
            $this->buildAllCombinationsFromModuleFlatEntries($moduleFlatEntries, $combinations, $index + 1, $newVersionList);
        }
    }

    /**
     * @param ModuleFlatEntryList $moduleFlatEntryList
     * @param array $combinations [compination, compination, compination ...]
     * @param string[] $compination [archiveName => version]
     * @param int $index
     */
    private function buildAllCombinationsFromModuleFlatEntryList(ModuleFlatEntryList $moduleFlatEntryList, array &$combinations, array $compination = [], int $index = 0)
    {
        /** @var ModuleFlatEntry*/
        $moduleFlatEntry = $moduleFlatEntryList->get($index);

        if (!$moduleFlatEntry) {
            $combinations[] = $compination;
            return;
        }

        foreach ($moduleFlatEntry->versions as $versionStr) {
            $version = [$moduleFlatEntry->archiveName => $versionStr];
            $newCombination = array_merge($compination, $version);
            $this->buildAllCombinationsFromModuleFlatEntryList($moduleFlatEntryList, $combinations, $newCombination, $index + 1);
        }
    }
}
