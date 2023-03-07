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

class FlatEntryBuilder
{
    /**
     * @param ModuleTree[] $moduleTrees
     *
     * @return FlatEntry[]
     */
    public function buildListFromModuleTrees(array $moduleTrees): array
    {
        $flatEntries = [];
        $this->buildModuleFlatEntriesByModuleTrees($moduleTrees, $flatEntries);
        return $flatEntries;
    }

    /**
     * @param ModuleTree $moduleTree
     *
     * @return FlatEntry[]
     */
    public function buildListFromModuleTree(ModuleTree $moduleTree): array
    {
        $flatEntries = [];
        $this->buildModuleFlatEntriesByModuleTree($moduleTree, $flatEntries);
        return $flatEntries;
    }

    /**
     * @param FlatEntry[] $flatEntries
     * @param string $archiveName
     * @param FlatEntry $flatEntry
     */
    private function addFlatEntry(array &$flatEntries, string $archiveName, FlatEntry $flatEntry): void
    {
        if (array_key_exists($archiveName, $flatEntries)) {
            $flatEntries[$archiveName]->combine($flatEntry);
        } else {
            $flatEntries[$archiveName] = $flatEntry;
        }
    }

    /**
     * @param ModuleTree[] $moduleTrees
     * @param FlatEntry[] $flatEntries
     */
    private function buildModuleFlatEntriesByModuleTrees(array $moduleTrees, array &$flatEntries): void
    {
        if (!$moduleTrees) {
            return;
        }

        foreach ($moduleTrees as $moduleTree) {
            $flatEntry = new FlatEntry();
            $flatEntry->archiveName = $moduleTree->archiveName;
            foreach ($moduleTree->moduleVersions as $moduleVersion) {
                $flatEntry->versions[] = $moduleVersion->version;
                $this->buildModuleFlatEntriesByModuleTrees($moduleVersion->require, $flatEntries);
            }
            $this->addFlatEntry($flatEntries, $moduleTree->archiveName, $flatEntry);
        }
    }

    /**
     * @param ModuleTree $moduleTree
     * @param FlatEntry[] $flatEntries
     */
    private function buildModuleFlatEntriesByModuleTree(ModuleTree $moduleTree, array &$flatEntries): void
    {
        $flatEntry = new FlatEntry();
        $flatEntry->archiveName = $moduleTree->archiveName;
        foreach ($moduleTree->moduleVersions as $moduleVersion) {
            $flatEntry->versions[] = $moduleVersion->version;
            foreach ($moduleVersion->require as $moduleTree) {
                $this->buildModuleFlatEntriesByModuleTree($moduleTree, $flatEntries);
            }
        }
        $this->addFlatEntry($flatEntries, $flatEntry->archiveName, $flatEntry);
    }

    /**
     * @param FlatEntry[] $moduleFlatTreeEntries
     * @param array $contraints
     * @return FlatEntry[]
     */
    public function removeFlatEntriesByContrains(array $moduleFlatTreeEntries, array $contraints): array
    {
        foreach ($contraints as $archiveName => $versions) {
            $moduleFlatTreeEntries = $this->removeModuleFlatEnty($moduleFlatTreeEntries, $archiveName, $versions);
        }
        return $moduleFlatTreeEntries;
    }

    /**
     * @param FlatEntry[] $moduleFlatTreeEntries
     * @param string $archiveName
     * @param string[] $versions
     *
     * @return FlatEntry[]
     */
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
            $newModuleFlatTreeEntry = new FlatEntry();
            $newModuleFlatTreeEntry->archiveName = $moduleFlatTreeEntry->archiveName;
            $newModuleFlatTreeEntry->versions = $fileredVersions;
            $filteredModuleFlatTreeEntries[$moduleFlatTreeEntry->archiveName] = $newModuleFlatTreeEntry;
        }
        return $filteredModuleFlatTreeEntries;
    }
}
