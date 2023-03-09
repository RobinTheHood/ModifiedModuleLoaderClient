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

use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;

class CombinationSatisfyer
{
    /** @var Comparator */
    private $comparator;

    public function __construct()
    {
        $this->comparator = new Comparator(new Parser());
    }

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

        // Es gibt keine weiteren Untermodule
        if (!$moduleTree->moduleVersions) {
            //var_dump($selectedVersion . ' == ' . $moduleTree->versionConstraint);
            return $this->comparator->satisfiesOr($selectedVersion, $moduleTree->versionConstraint);
        }

        foreach ($moduleTree->moduleVersions as $moduleVersion) {
            // Context: Version
            //var_dump($archiveName . " {$moduleVersion->version} == {$selectedVersion}");
            if ($moduleVersion->version === $selectedVersion) {
                //var_dump('x');
                return $this->satisfiesCominationFromModuleTrees($moduleVersion->require, $combination);
            }
        }
        // var_dump('xxx');
        // var_dump($moduleTree);
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
