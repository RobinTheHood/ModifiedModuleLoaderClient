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
use RobinTheHood\ModifiedModuleLoaderClient\SemverComparatorFactory;

class CombinationSatisfyer
{
    /** @var Comparator */
    private $comparator;

    public function __construct()
    {
        $this->comparator = SemverComparatorFactory::createComparator();
    }

    /**
     * @param ModuleTree[] $moduleTrees
     * @param Combination[] $combinations
     *
     * @return CombinationSatisfyerResult
     */
    public function satisfiesCominationsFromModuleTrees(
        array $moduleTrees,
        array $combinations
    ): CombinationSatisfyerResult {
        $testCombination = null;
        $foundCombination = new Combination();
        $failLog = new FailLog();

        foreach ($combinations as $testCombination) {
            $foundCombination = new Combination();
            $result = $this->satisfiesCombinationFromModuleTrees(
                $moduleTrees,
                $testCombination,
                $foundCombination,
                $failLog
            );

            if ($result) {
                $combinationSatisfyerResult = new CombinationSatisfyerResult(
                    CombinationSatisfyerResult::RESULT_COMBINATION_FOUND,
                    $testCombination,
                    $foundCombination,
                    $failLog
                );
                return $combinationSatisfyerResult;
            }
        }

        $combinationSatisfyerResult = new CombinationSatisfyerResult(
            CombinationSatisfyerResult::RESULT_COMBINATION_NOT_FOUND,
            $testCombination,
            $foundCombination,
            $failLog
        );

        return $combinationSatisfyerResult;
    }

    /**
     * @param ModuleTree $moduleTree
     * @param Combination[] $combinations
     *
     * @return CombinationSatisfyerResult
     */
    public function satisfiesCominationsFromModuleTree(
        ModuleTree $moduleTree,
        array $combinations
    ): CombinationSatisfyerResult {
        $testCombination = null;
        $foundCombination = new Combination();
        $failLog = new FailLog();

        foreach ($combinations as $testCombination) {
            $result = $this->satisfiesCombinationFromModuleTree(
                $moduleTree,
                $testCombination,
                $foundCombination,
                $failLog
            );

            if ($result) {
                $combinationSatisfyerResult = new CombinationSatisfyerResult(
                    CombinationSatisfyerResult::RESULT_COMBINATION_FOUND,
                    $testCombination,
                    $foundCombination,
                    $failLog
                );
                return $combinationSatisfyerResult;
            }
        }

        $combinationSatisfyerResult = new CombinationSatisfyerResult(
            CombinationSatisfyerResult::RESULT_COMBINATION_NOT_FOUND,
            $testCombination,
            $foundCombination,
            $failLog
        );

        return $combinationSatisfyerResult;
    }

    public function satisfiesCombinationsFromModuleWithIterator(
        ModuleTree $moduleTree,
        CombinationIterator $combinationIterator
    ): CombinationSatisfyerResult {
        while (true) {
            $foundCombination = new Combination();
            $failLog = new FailLog();
            $testCombination = $combinationIterator->current();
            $result = $this->satisfiesCombinationFromModuleTree(
                $moduleTree,
                $testCombination,
                $foundCombination,
                $failLog
            );

            if ($result) {
                $combinationSatisfyerResult = new CombinationSatisfyerResult(
                    CombinationSatisfyerResult::RESULT_COMBINATION_FOUND,
                    $testCombination,
                    $foundCombination,
                    $failLog
                );
                return $combinationSatisfyerResult;
            }

            $combinationIterator->next();
            if ($combinationIterator->isStart()) {
                $combinationSatisfyerResult = new CombinationSatisfyerResult(
                    CombinationSatisfyerResult::RESULT_COMBINATION_NOT_FOUND,
                    $testCombination,
                    $foundCombination,
                    $failLog
                );
                return $combinationSatisfyerResult;
            }
        }
    }

    /**
     * @param ModuleTree $moduleTree
     * @param Combination $combination
     * @param Combination $foundCombination
     * @param FailLog $failLog
     * @param ModuleTree[] $moduleTreeChain
     *
     * @return bool
     */
    public function satisfiesCombinationFromModuleTree(
        ModuleTree $moduleTree,
        Combination $combination,
        Combination &$foundCombination,
        FailLog &$failLog,
        array $moduleTreeChain = []
    ): bool {
        // Context: Module
        $archiveName = $moduleTree->archiveName;
        try {
            $selectedVersion = $combination->getVersion($archiveName);
        } catch (DependencyException $e) {
            return false;
        }

        // Es gibt keine weiteren Untermodule
        if (!$moduleTree->moduleVersions) {
            $result = $this->comparator->satisfies($selectedVersion, $moduleTree->versionConstraint);
            if (!$result) {
                $failLog->fail($moduleTreeChain, $archiveName, $selectedVersion, $moduleTree->versionConstraint);
            } else {
                $failLog->unfail($moduleTreeChain, $archiveName, $selectedVersion, $moduleTree->versionConstraint);
            }
            return $result;
        }

        foreach ($moduleTree->moduleVersions as $moduleVersion) {
            // Context: Version
            if ($moduleVersion->version === $selectedVersion) {
                $foundCombination->overwrite($archiveName, $moduleVersion->version);

                $failLog->unfail(
                    $moduleTreeChain,
                    $archiveName,
                    $moduleVersion->version,
                    $moduleTree->versionConstraint
                );

                return $this->satisfiesCombinationFromModuleTrees(
                    $moduleVersion->require,
                    $combination,
                    $foundCombination,
                    $failLog,
                    array_merge($moduleTreeChain, [$moduleTree])
                );
            }

            $failLog->fail($moduleTreeChain, $archiveName, $moduleVersion->version, $moduleTree->versionConstraint);
        }

        return false;
    }

    /**
     * @param ModuleTree[] $moduleTrees
     * @param Combination $combination
     * @param Combination $foundCombination
     * @param FailLog $failLog
     * @param ModuleTree[] $moduleTreeChain
     *
     * @return bool
     */
    public function satisfiesCombinationFromModuleTrees(
        array $moduleTrees,
        Combination $combination,
        Combination &$foundCombination,
        FailLog &$failLog,
        array $moduleTreeChain = []
    ): bool {
        // Context: Expanded
        $moduleResult = true;
        foreach ($moduleTrees as $moduleTree) {
            $result = $this->satisfiesCombinationFromModuleTree(
                $moduleTree,
                $combination,
                $foundCombination,
                $failLog,
                $moduleTreeChain
            );
            $moduleResult = $moduleResult && $result;
        }
        return $moduleResult;
    }
}
