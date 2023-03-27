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
     * @return CombinationSatisfyerResult
     */
    public function satisfiesCominationsFromModuleTrees(array $moduleTrees, array $combinations): CombinationSatisfyerResult
    {
        $foundCombination = new Combination();

        foreach ($combinations as $testCombination) {
            $foundCombination = new Combination();
            $result = $this->satisfiesCominationFromModuleTrees($moduleTrees, $testCombination, $foundCombination);
            if ($result) {
                $combinationSatisfyerResult = new CombinationSatisfyerResult(
                    $testCombination,
                    $foundCombination
                );
                return $combinationSatisfyerResult;
            }
        }
        $combinationSatisfyerResult = new CombinationSatisfyerResult(
            null,
            null
        );
        return $combinationSatisfyerResult;
    }

    /**
     * @param ModuleTree $moduleTree
     * @param Combination[] $combinations
     *
     * @return CombinationSatisfyerResult
     */
    public function satisfiesCominationsFromModuleTree(ModuleTree $moduleTree, array $combinations): CombinationSatisfyerResult
    {
        $foundCombination = new Combination();

        foreach ($combinations as $testCombination) {
            $result = $this->satisfiesCominationFromModuleTree($moduleTree, $testCombination, $foundCombination);
            if ($result) {
                $combinationSatisfyerResult = new CombinationSatisfyerResult(
                    $testCombination,
                    $foundCombination
                );
                return $combinationSatisfyerResult;
            }
        }
        $combinationSatisfyerResult = new CombinationSatisfyerResult(
            null,
            null
        );
        return $combinationSatisfyerResult;
    }

    public function satisfiesCominationsFromModuleWithIterator(
        ModuleTree $moduleTree,
        CombinationIterator $combinationIterator
    ): CombinationSatisfyerResult {
        $foundCombination = new Combination();

        while (true) {
            $testCombination = $combinationIterator->current();
            $result = $this->satisfiesCominationFromModuleTree($moduleTree, $testCombination, $foundCombination);

            if ($result) {
                $combinationSatisfyerResult = new CombinationSatisfyerResult(
                    $testCombination,
                    $foundCombination
                );
                return $combinationSatisfyerResult;
            }

            $combinationIterator->next();
            if ($combinationIterator->isStart()) {
                $combinationSatisfyerResult = new CombinationSatisfyerResult(
                    null,
                    null
                );
                return $combinationSatisfyerResult;
            }
        }
    }

    /**
     * @param ModuleTree $moduleTree
     * @param Combination $combination
     */
    public function satisfiesCominationFromModuleTree(ModuleTree $moduleTree, Combination $combination, Combination &$foundCombination): bool
    {
        // Context: Module
        $archiveName = $moduleTree->archiveName;
        try {
            $selectedVersion = $combination->getVersion($archiveName);
        } catch (DependencyException $e) {
            return false;
        }

        // Es gibt keine weiteren Untermodule
        if (!$moduleTree->moduleVersions) {
            //var_dump($selectedVersion . ' == ' . $moduleTree->versionConstraint);
            return $this->comparator->satisfies($selectedVersion, $moduleTree->versionConstraint);
        }

        foreach ($moduleTree->moduleVersions as $moduleVersion) {
            // Context: Version
            //var_dump($archiveName . " {$moduleVersion->version} == {$selectedVersion}");
            if ($moduleVersion->version === $selectedVersion) {
                $foundCombination->overwrite($archiveName, $moduleVersion->version);
                return $this->satisfiesCominationFromModuleTrees($moduleVersion->require, $combination, $foundCombination);
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
    public function satisfiesCominationFromModuleTrees(array $moduleTrees, Combination $combination, Combination &$foundCombination): bool
    {
        // Context: Expanded
        $moduleResult = true;
        foreach ($moduleTrees as $moduleTree) {
            $moduleResult =
                $moduleResult && $this->satisfiesCominationFromModuleTree($moduleTree, $combination, $foundCombination);
        }
        return $moduleResult;
    }
}
