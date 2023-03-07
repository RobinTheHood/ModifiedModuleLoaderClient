<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace RobinTheHood\ModifiedModuleLoaderClient\DependencyManager;

class CombinationSatisfyer
{
    /**
     * @param ModuleTree[] $moduleTrees
     * @param Combination[] $combinations
     *
     * @return Combination
     */
    public function satisfiesCominationsFromModuleTrees(array $moduleTrees, array $combinations): ?Combination
    {
        foreach ($combinations as $combination) {
            $result = $this->satisfiesCominationFromModuleTrees($moduleTrees, $combination);
            if ($result) {
                return $combination;
            }
        }
        return null;
    }

    /**
     * @param ModuleTree $moduleTree
     * @param Combination[] $combinations
     *
     * @return Combination
     */
    public function satisfiesCominationsFromModuleTree(ModuleTree $moduleTree, array $combinations): ?Combination
    {
        foreach ($combinations as $combination) {
            $result = $this->satisfiesCominationFromModuleTree($moduleTree, $combination);
            if ($result) {
                return $combination;
            }
        }
        return null;
    }

    public function satisfiesCominationsFromModuleWithIterator(
        ModuleTree $moduleTree,
        CombinationIterator $combinationIterator
    ): ?Combination {
        while (true) {
            $combination = $combinationIterator->current();
            $result = $this->satisfiesCominationFromModuleTree($moduleTree, $combination);
            if ($result) {
                return $combination;
            }

            $combinationIterator->next();
            if ($combinationIterator->isStart()) {
                return null;
            }
        }
    }

    /**
     * @param ModuleTree $moduleTree
     * @param Combination $combination
     */
    public function satisfiesCominationFromModuleTree(ModuleTree $moduleTree, Combination $combination): bool
    {
        // Context: Module
        $archiveName = $moduleTree->archiveName;
        $selectedVersion = $combination->getVersion($archiveName);
        foreach ($moduleTree->moduleVersions as $moduleVersion) {
            // Context: Version
            if ($moduleVersion->version === $selectedVersion) {
                return $this->satisfiesCominationFromModuleTrees($moduleVersion->require, $combination);
            }
        }
        return false;
    }

    /**
     * @param ModuleTree[] $moduleTrees
     * @param Combination $combination
     */
    public function satisfiesCominationFromModuleTrees(array $moduleTrees, Combination $combination): bool
    {
        // Context: Expanded
        $moduleResult = true;
        foreach ($moduleTrees as $moduleTree) {
            $moduleResult =
                $moduleResult && $this->satisfiesCominationFromModuleTree($moduleTree, $combination);
        }
        return $moduleResult;
    }
}
