<?php

declare(strict_types=1);

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher;

class HashEntryCollection
{
    /**
     * @var HashEntry[]
     */
    public $hashEntries = [];

    /**
     * @param HashEntry[] $hashEntries
     */
    public function __construct(array $hashEntries)
    {
        $this->hashEntries = $hashEntries;
    }

    /**
     * Liefert ein HashEntry anhand von $file zurück.
     */
    public function getByFile(string $file): ?HashEntry
    {
        foreach ($this->hashEntries as $hashEntry) {
            if ($hashEntry->file === $file) {
                return $hashEntry;
            }
        }
        return null;
    }

    /**
     * Gibt alle HashEntries zurück, deren Files nicht in $hashEntryCollection enthalten sind.
     *
     * @param HashEntryCollection $hashEntryCollection
     *
     * @return HashEntryCollection
     */
    public function getNotIn(HashEntryCollection $hashEntryCollection): HashEntryCollection
    {
        /** @var HashEntry[] */
        $hashEntries = [];
        foreach ($this->hashEntries as $hashEntry) {
            $foundHashEntry = $hashEntryCollection->getByFile($hashEntry->file);
            if (!$foundHashEntry) {
                $hashEntries[] = $hashEntry;
            }
        }
        return new HashEntryCollection($hashEntries);
    }

    /**
     * Gibt alle HashEntries zurück, deren File in hashEntryCollection enthalten
     * sind und bei denen die Hashes gleichzeitg unterschiedlich sind.
     *
     * @param HashEntryCollection $hashEntryCollection
     *
     * @return HashEntryCollection
     */
    public function getNotEqualTo(HashEntryCollection $hashEntryCollection): HashEntryCollection
    {
        /** @var HashEntry[] */
        $hashEntries = [];
        foreach ($this->hashEntries as $hashEntry) {
            $foundHashEntry = $hashEntryCollection->getByFile($hashEntry->file);
            if (!$foundHashEntry) {
                continue;
            }

            if ($hashEntry->hash !== $foundHashEntry->hash) {
                $hashEntries[] = $hashEntry;
            }
        }
        return new HashEntryCollection($hashEntries);
    }

    public function toArray(): array
    {
        $array = [];
        foreach ($this->hashEntries as $hashEntry) {
            $array[$hashEntry->file] = $hashEntry->hash;
        }
        return $array;
    }
}
