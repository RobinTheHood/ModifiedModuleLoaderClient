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

    public function getByType(int $type): ChangedEntryCollection
    {
        /** @var ChangedEntry[] */
        $changedEntries = [];
        foreach ($this->changedEntries as $changedEntry) {
            if ($changedEntry->type === $type) {
                $changedEntries[] = $changedEntry;
            }
        }
        return new ChangedEntryCollection($changedEntries);
    }

    /**
     * Erzeugt eine ChangedEntryCollection aus einer HashEntryCollection
     *
     * @param HashEntryCollection $hashEntryCollection
     * @param int $type ChangedEntry::TYPE_...
     */
    public static function createFromHashEntryCollection(
        HashEntryCollection $hashEntryCollection,
        int $type
    ): ChangedEntryCollection {
        /** @var ChangedEntry[] */
        $changedEntries = [];
        foreach ($hashEntryCollection->hashEntries as $hashEntry) {
            $changedEntries[] = ChangedEntry::createFromHashEntry($hashEntry, $type);
        }
        return new ChangedEntryCollection($changedEntries);
    }

    /**
     * Fügt mehrere ChangedEntryCollection zur einer zusammen.
     *
     * @param ChangedEntryCollection[] $changedEntryCollections
     */
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
