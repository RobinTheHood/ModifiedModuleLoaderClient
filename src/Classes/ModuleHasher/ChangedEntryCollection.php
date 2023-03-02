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

namespace RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher;

class ChangedEntryCollection
{
   /**
     * @var ChangedEntry[]
     */
    public $changedEntries = [];

    /**
     * @param ChangedEntry[] $hashEntries
     */
    public function __construct(array $changedEntries)
    {
        $this->changedEntries = $changedEntries;
    }

    public static function createFromHashEntryCollection(
        HashEntryCollection $hashEntryCollection,
        int $type
    ): ChangedEntryCollection {
        foreach ($hashEntryCollection->hashEntries as $hashEntry) {
            $changedEntries[] = ChangedEntry::createFromHashEntry($hashEntry, $type);
        }
        return new ChangedEntryCollection($changedEntries);
    }

    public static function merge(array $changedEntryCollections): ChangedEntryCollection
    {
        /** @var ChangedEntry[]*/
        $changedEntries = [];

        foreach ($changedEntryCollections as $changedEntryCollection) {
            $changedEntries = array_merge($changedEntries, $changedEntryCollection->changedEntries);
        }

        return new ChangedEntryCollection($changedEntries);
    }
}
