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

use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;

class Comparator
{
    protected $parser;

    public function __construct(Parser $parser)
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

        if ($versionString1 == 'auto' && $versionString2 == 'auto') {
            return true;
        }

        $version1 = $this->parser->parse($versionString1);
        $version2 = $this->parser->parse($versionString2);

        if ($version1->getMajor() > $version2->getMajor()) {
            return true;
        }

        if (
            $version1->getMajor() == $version2->getMajor() &&
            $version1->getMinor() > $version2->getMinor()
        ) {
            return true;
        }

        if (
            $version1->getMajor() == $version2->getMajor() &&
            $version1->getMinor() == $version2->getMinor() &&
            $version1->getPatch() > $version2->getPatch()
        ) {
            return true;
        }

        if (
            $version1->getMajor() == $version2->getMajor() &&
            $version1->getMinor() == $version2->getMinor() &&
            $version1->getPatch() == $version2->getPatch() &&
            (new TagComparator())->greaterThan($version1->getTag(), $version2->getTag())
        ) {
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

        if ($version1->getMajor() !== $version2->getMajor()) {
            return false;
        }

        if ($version1->getMinor() !== $version2->getMinor()) {
            return false;
        }

        if ($version1->getPatch() !== $version2->getPatch()) {
            return false;
        }

        if ($version1->getTag() !== $version2->getTag()) {
            return false;
        }

        return true;
    }

    public function greaterThanOrEqualTo(string $versionString1, string $versionString2): bool
    {
        if ($this->greaterThan($versionString1, $versionString2)) {
            return true;
        }

        if ($this->equalTo($versionString1, $versionString2)) {
            return true;
        }

        return false;
    }

    public function lessThan(string $versionString1, string $versionString2): bool
    {
        if (!$this->greaterThanOrEqualTo($versionString1, $versionString2)) {
            return true;
        }

        return false;
    }

    public function lessThanOrEqualTo(string $versionString1, string $versionString2): bool
    {
        if (!$this->greaterThan($versionString1, $versionString2)) {
            return true;
        }

        return false;
    }

    public function notEqualTo(string $versionString1, string $versionString2): bool
    {
        if (!$this->equalTo($versionString1, $versionString2)) {
            return true;
        }

        return false;
    }

    public function highest(array $versionStrings): string
    {
        $versionStrings = $this->rsort($versionStrings);
        return $versionStrings[0];
    }

    public function lowest(array $versionStrings): string
    {
        $versionStrings = $this->sort($versionStrings);
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

        if ($version1->getMajor() != $version2->getMajor()) {
            return false;
        }

        return $this->greaterThanOrEqualTo($versionString1, $versionString2);
    }

    public function satisfies(string $versionString1, string $constrain): bool
    {
        if ($constrain[0] == '^') { // Ist Buchstabe an Index 0 = ^
            $versionString2 = str_replace('^', '', $constrain);
            return $this->isCompatible($versionString1, $versionString2);
        } else {
            $versionString2 = $constrain;
            return $this->equalTo($versionString1, $versionString2);
        }
    }

    /**
     * Can satisfy multiple constraints with OR / ||
     *
     * Example: ^7.4 || ^8.0
     */
    public function satisfiesOr(string $versionString1, string $constraintOrExpression): bool
    {
        $constraints = explode('||', $constraintOrExpression);
        foreach ($constraints as $constraint) {
            $constraint = trim($constraint);
            if ($this->satisfies($versionString1, $constraint)) {
                return true;
            }
        }
        return false;
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

    public function filterVersionsByConstraint(string $constraint, array $versions): array
    {
        $fileredVersions = [];
        foreach ($versions as $version) {
            if ($this->satisfiesOr($version, $constraint)) {
                $fileredVersions[] = $version;
            }
        }
        return $fileredVersions;
    }

    public function getLatestVersionByConstraint(string $constraint, array $versions): string
    {
        $filteredVersions = $this->filterVersionsByConstraint($constraint, $versions);
        if (!$filteredVersions) {
            return '';
        }
        $filteredVersions = $this->rsort($filteredVersions);

        return $filteredVersions[0] ?? '';
    }

    public function getOldestVersionByConstraint(string $constraint, array $versions): string
    {
        $filteredVersions = $this->filterVersionsByConstraint($constraint, $versions);
        if (!$filteredVersions) {
            return '';
        }
        $filteredVersions = $this->sort($filteredVersions);

        return $filteredVersions[0] ?? '';
    }

    public function filterStable(array $versionStrings): array
    {
        $fileredVersionStrings = [];
        foreach ($versionStrings as $versionString) {
            $version = $this->parser->parse($versionString);
            if (!$version->getTag()) {
                $fileredVersionStrings[] = $versionString;
            }
        }
        return $fileredVersionStrings;
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
