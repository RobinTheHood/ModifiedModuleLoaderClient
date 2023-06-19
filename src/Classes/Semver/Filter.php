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

class Filter
{
    private $sorter;

    private $comparator;

    private $parser;

    public static function create(int $mode): Filter
    {
        $parser = Parser::create();
        $comparator = Comparator::create($mode);
        $sorter = Sorter::create($mode);
        $filter = new Filter($parser, $comparator, $sorter);
        return $filter;
    }

    public function __construct(Parser $parser, Comparator $comparator, Sorter $sorter)
    {
        $this->parser = $parser;
        $this->comparator = $comparator;
        $this->sorter = $sorter;
    }

    public function latest(array $versionStrings): string
    {
        if (!$versionStrings) {
            return '';
        }
        $versionStrings = $this->sorter->rsort($versionStrings);
        return $versionStrings[0];
    }

    public function oldest(array $versionStrings): string
    {
        if (!$versionStrings) {
            return '';
        }
        $versionStrings = $this->sorter->sort($versionStrings);
        return $versionStrings[0];
    }

    public function byConstraint(string $constraint, array $versions): array
    {
        $fileredVersions = [];
        foreach ($versions as $version) {
            if ($this->comparator->satisfies($version, $constraint)) {
                $fileredVersions[] = $version;
            }
        }
        return $fileredVersions;
    }

    public function latestByConstraint(string $constraint, array $versions): string
    {
        $filteredVersions = $this->byConstraint($constraint, $versions);
        return $this->latest($filteredVersions);
    }

    public function oldestByConstraint(string $constraint, array $versions): string
    {
        $filteredVersions = $this->byConstraint($constraint, $versions);
        return $this->oldest($filteredVersions);
    }

    public function stable(array $versionStrings): array
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
}
