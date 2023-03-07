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
    /** @var ModuleFlatEntry[] */
    private $moduleFlatEntries;

    /** @var int[] */

    /** @var Counter $counter */
    private $counter;

    /**
     * @param ModuleFlatEntry[] $moduleFlatEntries
     */
    public function __construct(array $moduleFlatEntries)
    {
        $this->moduleFlatEntries = $moduleFlatEntries;

        foreach ($moduleFlatEntries as $moduleFlatEntry) {
            $counterMaxValues[] = count($moduleFlatEntry->versions) - 1;
        }

        $this->counter = new Counter($counterMaxValues);
    }

    public function current(): array
    {
        return $this->combinationFromCounter($this->moduleFlatEntries, $this->counter);
    }

    public function next(): array
    {
        $this->counter->next();
        return $this->combinationFromCounter($this->moduleFlatEntries, $this->counter);
    }

    public function isStart(): bool
    {
        return $this->counter->isStart();
    }

    /**
     * @param ModuleFlatEntry[] $moduleFlatEntries
     */
    private function combinationFromCounter(array $moduleFlatEntries, Counter $counter): array
    {
        $counter = $counter->current();

        $counterIndex = 0;
        $combination = [];
        foreach ($moduleFlatEntries as $moduleFlatEntry) {
            $versionIndex = $counter[$counterIndex];
            $versionStr = $moduleFlatEntry->versions[$versionIndex];
            $version = [$moduleFlatEntry->archiveName => $versionStr];
            $combination = array_merge($combination, $version);
            $counterIndex++;
        }

        return $combination;
    }
}
