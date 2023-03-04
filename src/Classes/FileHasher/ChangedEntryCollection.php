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

namespace RobinTheHood\ModifiedModuleLoaderClient\FileHasher;

class ChangedEntryCollection
{
   /**
     * @var ChangedEntry[]
     */
    public $changedEntries = [];

    /**
     * @param ChangedEntry[] $changedEntries
     */
    public function __construct(array $changedEntries)
    {
        $this->changedEntries = $changedEntries;
    }

    /**
     * Liefert ein ChangedEntry anhand von $file zurück.
     */
    public function getByFileA(string $file): ?ChangedEntry
    {
        foreach ($this->changedEntries as $changedEntry) {
            if ($changedEntry->hashEntryA->file === $file) {
                return $changedEntry;
            }
        }
        return null;
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


    public function add(ChangedEntry $changedEntry): void
    {
        $this->changedEntries[] = $changedEntry;
    }

    public function unique(): ChangedEntryCollection
    {
        $newChangeEntryCollection = new ChangedEntryCollection([]);
        foreach ($this->changedEntries as $changedEntry) {
            if ($newChangeEntryCollection->getByFileA($changedEntry->hashEntryA->file)) {
                continue;
            }
            $newChangedEntry = $changedEntry->clone();
            $newChangeEntryCollection->add($newChangedEntry);
        }
        return $newChangeEntryCollection;
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
