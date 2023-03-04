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

class FileHasher implements FileHasherInterface
{
    /**
     * @param string[] paths
     */
    public function createHashes(array $paths, string $basePath = '', string $scope = ''): HashEntryCollection
    {
        $hashEntries = [];
        foreach ($paths as $path) {
            $src = $basePath . $path;
            if (!file_exists($src)) {
                continue;
            }

            if (is_dir($src)) {
                continue;
            }

            $hash = $this->createHash($src);

            $hashEntry = new HashEntry();
            $hashEntry->file = $path;
            $hashEntry->scope = $scope;
            $hashEntry->hash = $hash;

            $hashEntries[] = $hashEntry;
        }

        return new HashEntryCollection($hashEntries);
    }

    private function createHash(string $path): string
    {
        return md5_file($path);
    }
}
