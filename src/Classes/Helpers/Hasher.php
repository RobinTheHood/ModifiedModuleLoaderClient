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
    // public function createHashFile($path, $hashes)
    // {
    //     $jsonStr = json_encode($hashes, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    //     file_put_contents($path, $jsonStr);
    // }

    // public function deleteHashFile($path)
    // {
    //     if (file_exists($path)) {
    //         unlink($path);
    //     }
    // }

    // public function loadHashes($path)
    // {
    //     if (!file_exists($path)) {
    //         return [];
    //     }

    //     $jsonStr = file_get_contents($path);
    //     $hashes = json_decode($jsonStr, true);
    //     return $hashes;
    // }

    // public function createFileHashes($files, $root = '')
    // {
    //     $hashes = [];

    //     foreach ($files as $file) {
    //         $src = $root . $file;
    //         if (file_exists($src)) {
    //             $hashes[$file] = md5_file($src);
    //         }
    //     }

    //     return $hashes;
    // }

    // public function getChanges($hashesLoaded, $hashesCreatedA, $hashesCreatedB)
    // {
    //     $changedFiles = [];
    //     foreach ($hashesLoaded as $file => $hashLoaded) {
    //         if (!isset($hashesCreatedA[$file])) {
    //             $changedFiles[$file] = 'deleted';
    //             continue;
    //         }

    //         $hashCreated = $hashesCreatedA[$file];

    //         if ($hashCreated != $hashLoaded) {
    //             $changedFiles[$file] = 'changed';
    //         }
    //     }

    //     foreach ($hashesCreatedB as $file => $hashCreated) {
    //         if (!isset($hashesLoaded[$file])) {
    //             $changedFiles[$file] = 'new';
    //             continue;
    //         }

    //         $hashLoaded = $hashesLoaded[$file];

    //         if ($hashCreated != $hashLoaded) {
    //             $changedFiles[$file] = 'changed';
    //         }
    //     }

    //     return $changedFiles;
    // }
}
