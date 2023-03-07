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

class ModuleFlatEntryList
{
    /** @var ModuleFlatEntry[] */
    private $moduleFlatEntries = [];

    /**
     * @param ModuleFlatEntry[] $moduleFlatEntries
     */
    public function __construct(array $moduleFlatEntries)
    {
        $this->moduleFlatEntries = array_values($moduleFlatEntries);
    }

    public function get(int $index): ?ModuleFlatEntry
    {
        return $this->moduleFlatEntries[$index] ?? null;
    }

    public function getAll(): array
    {
        return $this->moduleFlatEntries;
    }
}
