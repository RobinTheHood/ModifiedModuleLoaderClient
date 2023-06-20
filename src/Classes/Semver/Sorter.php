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

namespace RobinTheHood\ModifiedModuleLoaderClient\Semver;

class Sorter
{
    /** @var Comparator */
    private $comparator;

    public static function create(int $mode): Sorter
    {
        $comparator = Comparator::create($mode);
        $sorter = new Sorter($comparator);
        return $sorter;
    }

    public function __construct(Comparator $comparator)
    {
        $this->comparator = $comparator;
    }

    public function sort(array $versionStrings): array
    {
        usort($versionStrings, [$this, 'compareAsc']);
        return $versionStrings;
    }

    public function rsort(array $versionStrings): array
    {
        usort($versionStrings, [$this, 'compareDes']);
        return $versionStrings;
    }

    private function compareAsc(string $versionString1, string $versionString2): int
    {
        if ($this->comparator->greaterThan($versionString1, $versionString2)) {
            return 1;
        }

        return -1;
    }

    private function compareDes(string $versionString1, string $versionString2): int
    {
        if ($this->comparator->greaterThan($versionString1, $versionString2)) {
            return -1;
        }

        return 1;
    }
}
