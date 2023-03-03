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

class HashFileFactory
{
    public static function createFromArray(array $array): HashFile
    {
        $hashFile = new HashFile();
        $hashFile->array = $array;
        return $hashFile;
    }

    public static function createFromJson(string $json, string $defaultScope = 'root'): HashFile
    {
        $array = json_decode($json, true);

        $version = self::getVersion($array);
        if ($version === '0.1.0') {
            $array = self::convertToVersion020($array, $defaultScope);
        }

        return self::createFromArray($array);
    }

    private static function getVersion(array $array): string
    {
        if (array_key_exists('version', $array)) {
            return $array['version'];
        }

        if (array_key_exists('scopes', $array)) {
            if (is_array($array['scopes'])) {
                return '0.2.0';
            }
        }

        // Version 0.1.0 is the modulehash.json Version of MMLC <= 1.20.0
        // This modulehash.json has no version information an is internal
        // handelt as version 0.1.0.
        return '0.1.0';
    }

    private static function convertToVersion020(array $array, string $scope): array
    {
        $newArray = [
            'scopes' => [
                $scope => [
                    'hashes' => $array
                ]
            ]
        ];
        return $newArray;
    }
}
