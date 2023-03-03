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

class HashFile
{
    public $array;

    public function getScope(string $name): array
    {
        return $this->array['scopes'][$name] ?? [];
    }

    public function getScopeHashes(string $name): HashEntryCollection
    {
        $scope = $this->getScope($name);
        if (!$scope) {
            return new HashEntryCollection([]);
        }

        $hashes = $scope['hashes'] ?? [];
        if (!$hashes) {
            return new HashEntryCollection([]);
        }

        $hashEntries = [];
        foreach ($hashes as $file => $hash) {
            $hashEntry = new HashEntry();
            $hashEntry->file = $file;
            $hashEntry->hash = $hash;
            $hashEntries[] = $hashEntry;
        }

        return new HashEntryCollection($hashEntries);
    }
}
