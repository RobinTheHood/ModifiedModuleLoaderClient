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

namespace RobinTheHood\ModifiedModuleLoaderClient\DependencyManager;

use Exception;

class Combination
{
    /** @var array */
    public $combinations = [];

    public function add(string $archiveName, string $version)
    {
        if (array_key_exists($archiveName, $this->combinations)) {
            throw new Exception($archiveName . ' is already set.');
        }

        $this->combinations[$archiveName] = $version;
    }

    public function overwrite(string $archiveName, string $version)
    {
        $this->combinations[$archiveName] = $version;
    }

    public function getVersion(string $archiveName): string
    {
        if (!array_key_exists($archiveName, $this->combinations)) {
            throw new Exception('Version of ' . $archiveName . ' not found.');
        }

        return $this->combinations[$archiveName];
    }

    public function clone(): Combination
    {
        $combinations = $this->combinations; // clones an array
        $newCombination = new Combination();
        $newCombination->combinations = $combinations;
        return $newCombination;
    }

    /**
     * Liefert eine neues Combinations Obj zurück, in der nur echte Module enthalten sind.
     */
    public function strip(): Combination
    {
        $combination = new Combination();
        foreach ($this->combinations as $archiveName => $version) {
            if (strpos($archiveName, '/') === false) { // Überspringe Einträge wie modified, php, mmlc ...
                continue;
            }

            $combination->add($archiveName, $version);
        }
        return $combination;
    }
}
