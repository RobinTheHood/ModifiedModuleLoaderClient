<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient\Helpers;

class Hasher
{
    public function createHashFile($path, $hashes)
    {
        $jsonStr = json_encode($hashes, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        file_put_contents($path, $jsonStr);
    }

    public function deleteHashFile($path)
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    public function loadHashes($path)
    {
        if (!file_exists($path)) {
            return [];
        }

        $jsonStr = file_get_contents($path);
        $hashes = json_decode($jsonStr, true);

        if (!array_key_exists('scopes', $hashes)) {
            $hashes = [
                'scopes' => [
                    'src' => [
                        'hashes' => $hashes
                    ]
                ]
            ];
        }

        // var_dump($hashes);
        // die();
        return $hashes;
    }

    public function createFileHashes($files, $root = '')
    {
        $hashes = [];

        foreach ($files as $file) {
            $src = $root . $file;
            if (file_exists($src)) {
                $hashes[$file] = md5_file($src);
            }
        }

        return $hashes;
    }

    public function getChanges($hashesLoaded, $hashesCreatedA, $hashesCreatedB)
    {
        $changedFiles = [];
        foreach ($hashesLoaded as $file => $hashLoaded) {
            if (!isset($hashesCreatedA[$file])) {
                $changedFiles[$file] = 'deleted';
                continue;
            }

            $hashCreated = $hashesCreatedA[$file];

            if ($hashCreated != $hashLoaded) {
                $changedFiles[$file] = 'changed';
            }
        }

        foreach ($hashesCreatedB as $file => $hashCreated) {
            if (!isset($hashesLoaded[$file])) {
                $changedFiles[$file] = 'new';
                continue;
            }

            $hashLoaded = $hashesLoaded[$file];

            if ($hashCreated != $hashLoaded) {
                $changedFiles[$file] = 'changed';
            }
        }

        return $changedFiles;
    }

    /**
     *
     * HashEntries = [
     *     [file => hash],
     *     [file => hash],
     *     ...
     * ]
     *
     * ChangeEntries = [
     *     [file => type],
     *     [file => type],
     *     ...
     * ]
     *
     * @param array $installed HashEntires
     * @param array $shop HashEntires
     * @param array $mmlc HashEntires
     *
     * @return array ChangeEntries
     */
    public function getChangedEntries(array $installed, array $shop, array $mmlc): array
    {
        $new = $this->getAllHashesANotInB($mmlc, $installed);
        $deleted = $this->getAllHashesANotInB($installed, $shop);
        $changed1 = $this->getHashesEntriesANotEqualToB($installed, $shop);
        $changed2 = $this->getHashesEntriesANotEqualToB($mmlc, $installed);

        $changeEntries = [];
        $changeEntries = $this->addChanges($changeEntries, $new, 'new');
        $changeEntries = $this->addChanges($changeEntries, $deleted, 'deleted');
        $changeEntries = $this->addChanges($changeEntries, $changed1, 'changed');
        $changeEntries = $this->addChanges($changeEntries, $changed2, 'changed');

        return $changeEntries;
    }

    private function addChanges(array $changeEntries, array $hashEntires, string $type)
    {
        foreach ($hashEntires as $file => $hash) {
            $changeEntries[$file] = $type;
        }
        return $changeEntries;
    }

    /**
     * Gibt alle HashEntries aus A zurück, deren File nicht in B enthalten sind.
     * HashEntries = [
     *     [file => hash],
     *     [file => hash],
     *     ...
     * ]
     */
    private function getAllHashesANotInB(array $hashesEntriesA, array $hashesEntriesB): array
    {
        $hashEntries = [];
        foreach ($hashesEntriesA as $fileA => $hashA) {
            if (!isset($hashesEntriesB[$fileA])) {
                $hashes[] = $hashesEntriesA;
            }
        }
        return $hashEntries;
    }

    /**
     * Gibt alle HashEntries aus A zurück, deren File in B enthalten ist und die
     * Hashes von A und B unterschiedlich sind.
     * HashEntries = [
     *     [file => hash],
     *     [file => hash],
     *     ...
     * ]
     */
    private function getHashesEntriesANotEqualToB(array $hashesEntriesA, array $hashesEntriesB): array
    {
        $hashEntries = [];
        foreach ($hashesEntriesA as $fileA => $hashA) {
            if (!isset($hashesEntriesB[$fileA])) {
                continue;
            }

            if ($hashA !== $hashesEntriesB[$fileA]) {
                $hashEntries[] = $hashA;
            }
        }
        return $hashEntries;
    }
}
