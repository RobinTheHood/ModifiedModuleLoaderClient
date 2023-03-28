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

class Combination
{
    /** @var array */
    public $combinations = [];

    public function add(string $archiveName, string $version)
    {
        if (array_key_exists($archiveName, $this->combinations)) {
            throw new DependencyException($archiveName . ' is already set.');
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
            throw new DependencyException('Version of ' . $archiveName . ' not found.');
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
     * Liefert eine neues Combinations Obj zurück, in der nur echte Module enthalten sind
     *
     * Einträge, wie modified, php, mmlc werden entfernt.
     *
     * @return Combination Liefert ein Combination Obj nur mit echten Modulen
     */
    public function strip(): Combination
    {
        $combination = new Combination();
        foreach ($this->combinations as $archiveName => $version) {
            if (!$this->isArchiveName($archiveName)) {
                continue;
            }

            $combination->add($archiveName, $version);
        }
        return $combination;
    }

    /**
     * Überprüft, ob es sich um einen gültigen ArchvieName handelt
     *
     * @param string $archiveName Ein ArchiveName
     * @return bool Liefert true, wenn $archiveName ein gültiger ArchvieName ist
     */
    private function isArchiveName(string $archiveName): bool
    {
        if (strpos($archiveName, '/') === false) {
            return false;
        }

        return true;
    }


    public function __toString(): string
    {
        $strings = [];
        foreach ($this->combinations as $archiveName => $version) {
            $strings[] = "$archiveName v$version";
        }
        return implode(', ', $strings);
    }
}
