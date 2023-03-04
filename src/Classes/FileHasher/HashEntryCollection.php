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

namespace RobinTheHood\ModifiedModuleLoaderClient\FileHasher;

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

    public function toArray(): array
    {
        $array = [];
        foreach ($this->hashEntries as $hashEntry) {
            $array[$hashEntry->file] = $hashEntry->hash;
        }
        return $array;
    }
}
