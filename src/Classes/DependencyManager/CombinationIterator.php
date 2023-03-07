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

class CombinationIterator
{
    /** @var FlatEntry[] */
    private $flatEntries;

    /** @var int[] */

    /** @var Counter $counter */
    private $counter;

    /**
     * @param FlatEntry[] $flatEntries
     */
    public function __construct(array $flatEntries)
    {
        $this->flatEntries = $flatEntries;

        foreach ($flatEntries as $flatEntry) {
            $counterMaxValues[] = count($flatEntry->versions) - 1;
        }

        $this->counter = new Counter($counterMaxValues);
    }

    public function current(): Combination
    {
        return $this->combinationFromCounter($this->flatEntries, $this->counter);
    }

    public function next(): Combination
    {
        $this->counter->next();
        return $this->combinationFromCounter($this->flatEntries, $this->counter);
    }

    public function isStart(): bool
    {
        return $this->counter->isStart();
    }

    /**
     * @param FlatEntry[] $flatEntries
     * @param Counter Counter
     */
    private function combinationFromCounter(array $flatEntries, Counter $counter): Combination
    {
        $counter = $counter->current();

        $counterIndex = 0;
        $combination = new Combination();
        foreach ($flatEntries as $flatEntry) {
            $versionIndex = $counter[$counterIndex];
            $versionStr = $flatEntry->versions[$versionIndex];
            $combination->add($flatEntry->archiveName, $versionStr);
            $counterIndex++;
        }

        return $combination;
    }
}
