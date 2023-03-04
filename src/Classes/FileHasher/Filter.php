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

class Filter
{
    /**
     * Gibt alle ChangedEntries zurück, deren Files A nicht in B enthalten sind.
     *
     * @param HashEntryCollection $hashEntryCollectionA
     * @param HashEntryCollection $hashEntryCollectionB
     *
     * @return ChangedEntryCollection
     */
    public function getANotInB(
        HashEntryCollection $hashEntryCollectionA,
        HashEntryCollection $hashEntryCollectionB,
        int $type
    ): ChangedEntryCollection {
        /** @var ChangedEntry[] */
        $changedEntries = [];
        foreach ($hashEntryCollectionA->hashEntries as $hashEntry) {
            $foundHashEntry = $hashEntryCollectionB->getByFile($hashEntry->file);
            if (!$foundHashEntry) {
                $changedEntries[] = ChangedEntry::createFromHashEntry($type, $hashEntry, null);
            }
        }
        return new ChangedEntryCollection($changedEntries);
    }

    /**
     * Gibt alle ChangedEntries zurück, deren File in A und B enthalten
     * sind und bei denen die Hashes gleichzeitg unterschiedlich sind.
     *
     * @param HashEntryCollection $hashEntryCollectionA
     * @param HashEntryCollection $hashEntryCollectionB
     *
     * @return ChangedEntryCollection
     */
    public function getANotEqualToB(
        HashEntryCollection $hashEntryCollectionA,
        HashEntryCollection $hashEntryCollectionB,
        int $type
    ): ChangedEntryCollection {
        /** @var ChangedEntry[] */
        $changedEntries = [];
        foreach ($hashEntryCollectionA->hashEntries as $hashEntry) {
            $foundHashEntry = $hashEntryCollectionB->getByFile($hashEntry->file);
            if (!$foundHashEntry) {
                continue;
            }

            if ($hashEntry->hash !== $foundHashEntry->hash) {
                $changedEntries[] = ChangedEntry::createFromHashEntry($type, $hashEntry, $foundHashEntry);
            }
        }
        return new ChangedEntryCollection($changedEntries);
    }
}
