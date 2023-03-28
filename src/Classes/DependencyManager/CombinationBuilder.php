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

class CombinationBuilder
{
    /**
     * @param FlatEntry[] $flatEntries
     *
     * @return Combination[]
     */
    public function buildAllFromModuleFlatEntries($flatEntries): array
    {
        $combinations = [];
        $flatEntries = array_values($flatEntries);
        $this->buildAllCombinationsFromModuleFlatEntries($flatEntries, $combinations, new Combination(), 0);
        return $combinations;
    }

    /**
     * @param FlatEntry[] $flatEntries
     * @param Combination[] $combinations
     * @param Combination $combination
     * @param int $index
     */
    private function buildAllCombinationsFromModuleFlatEntries(
        array &$flatEntries,
        array &$combinations,
        Combination $combination,
        int $index
    ): void {
        /** @var FlatEntry*/
        $flatEntry = $flatEntries[$index] ?? [];

        if (!$flatEntry) {
            $combinations[] = $combination;
            return;
        }

        foreach ($flatEntry->versions as $versionStr) {
            $newCombination = $combination->clone();
            $newCombination->add($flatEntry->archiveName, $versionStr);
            $this->buildAllCombinationsFromModuleFlatEntries($flatEntries, $combinations, $newCombination, $index + 1);
        }
    }
}
