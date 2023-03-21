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

namespace RobinTheHood\ModifiedModuleLoaderClient\Tests\Unit\SemverTests;

use PHPUnit\Framework\TestCase;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Sorter;

class SemverSorterTest extends TestCase
{
    /** @var Sorter */
    public $sorter;

    protected function setUp(): void
    {
        $comparator = new Comparator(new Parser());
        $this->sorter = new Sorter($comparator);
    }

    public function testSemverCanSortVersions()
    {
        $versions = [
            '17.111.9',
            '1.2.3',
            '18.22.10',
            '18.33.10',
            '18.22.9'
        ];

        $expected = [
            '1.2.3',
            '17.111.9',
            '18.22.9',
            '18.22.10',
            '18.33.10'
        ];

        $this->assertEquals($expected, $this->sorter->sort($versions));
    }

    public function testSemverCanSortReverseVersions()
    {
        $versions = [
            '17.111.9',
            '1.2.3',
            '18.22.10',
            '18.33.10',
            '18.22.9'
        ];

        $expected = [
            '18.33.10',
            '18.22.10',
            '18.22.9',
            '17.111.9',
            '1.2.3'
        ];

        $this->assertEquals($expected, $this->sorter->rsort($versions));
    }
}
