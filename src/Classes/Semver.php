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

namespace RobinTheHood\ModifiedModuleLoaderClient;

class Semver
{
    protected $parser;

    public function __construct(SemverParser $parser)
    {
        $this->parser = $parser;
    }

    public function greaterThan(string $versionString1, string $versionString2): bool
    {
        if ($versionString1 == 'auto' && $versionString2 != 'auto') {
            return true;
        }

        if ($versionString2 == 'auto' && $versionString1 != 'auto') {
            return false;
        }

        $version1 = $this->parser->parse($versionString1);
        $version2 = $this->parser->parse($versionString2);

        if ($version1['major'] > $version2['major']) {
            return true;
        }

        if ($version1['major'] == $version2['major'] &&
            $version1['minor'] > $version2['minor'] ) {
            return true;
        }

        if ($version1['major'] == $version2['major'] &&
            $version1['minor'] == $version2['minor'] &&
            $version1['patch'] > $version2['patch'] ) {
            return true;
        }

        return false;
    }

    public function equalTo(string $versionString1, string $versionString2): bool
    {
        if ($versionString1 == 'auto' && $versionString2 == 'auto') {
            return true;
        } elseif ($versionString1 == 'auto' && $versionString2 != 'auto') {
            return false;
        } elseif ($versionString1 != 'auto' && $versionString2 == 'auto') {
            return false;
        }

        $version1 = $this->parser->parse($versionString1);
        $version2 = $this->parser->parse($versionString2);

        if ($version1['major'] != $version2['major']) {
            return false;
        }

        if ($version1['minor'] != $version2['minor'] ) {
            return false;
        }

        if ($version1['patch'] != $version2['patch'] ) {
            return false;
        }

        return true;
    }

    public function greaterThanOrEqualTo(string $versionString1, string $versionString2): bool
    {
        if (self::greaterThan($versionString1, $versionString2)) {
            return true;
        }

        if (self::equalTo($versionString1, $versionString2)) {
            return true;
        }

        return false;
    }

    public function lessThan(string $versionString1, string $versionString2): bool
    {
        if (!self::greaterThanOrEqualTo($versionString1, $versionString2)) {
            return true;
        }

        return false;
    }

    public function lessThanOrEqualTo(string $versionString1, string $versionString2): bool
    {
        if (!self::greaterThan($versionString1, $versionString2)) {
            return true;
        }

        return false;
    }

    public function notEqualTo(string $versionString1, string $versionString2): bool
    {
        if (!self::equalTo($versionString1, $versionString2)) {
            return true;
        }

        return false;
    }

    public function highest(array $versionStrings): string
    {
        $versionStrings = self::rsort($versionStrings);
        return $versionStrings[0];
    }

    public function lowest(array $versionStrings): string
    {
        $versionStrings = self::sort($versionStrings);
        return $versionStrings[0];
    }

    // Testet ob Version1 mindestens das kann, was auch Version2 kann.
    // Version1 darf auch mehr können als das was Version2 kann,
    // aber nicht weniger.
    public function isCompatible(string $versionString1, string $versionString2): bool
    {
        if ($versionString1 == 'auto') {
            return true;
        }

        $version1 = $this->parser->parse($versionString1);
        $version2 = $this->parser->parse($versionString2);

        if ($version1['major'] != $version2['major']) {
            return false;
        }

        return self::greaterThanOrEqualTo($versionString1, $versionString2);
    }

    public function satisfies(string $versionString1, string $constrain): bool
    {
        if ($constrain[0] == '^') { // Ist Buchstabe an Index 0 = ^
            $versionString2 = str_replace('^', '', $constrain);
            return self::isCompatible($versionString1, $versionString2);
        } else {
            $versionString2 = $constrain;
            return self::equalTo($versionString1, $versionString2);
        }
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
        if ($this->greaterThan($versionString1, $versionString2)) {
            return 1;
        }

        return -1;
    }

    private function compareDes(string $versionString1, string $versionString2): int
    {
        if ($this->greaterThan($versionString1, $versionString2)) {
            return -1;
        }

        return 1;
    }
}
