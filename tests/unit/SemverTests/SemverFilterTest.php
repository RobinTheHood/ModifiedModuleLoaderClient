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
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Filter;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Sorter;

class SemverFilterTest extends TestCase
{
    /** @var Filter */
    public $filter;

    protected function setUp(): void
    {
        $this->filter = Filter::create(Comparator::CARET_MODE_STRICT);
    }

    public function testSemverGetsHighestVersionString()
    {
        $versions = [
            '17.111.9',
            '1.2.3',
            '18.22.10',
            '18.33.10',
            '18.22.9'
        ];

        $this->assertEquals('18.33.10', $this->filter->latest($versions));
    }

    public function testSemverGetsLowestVersionString()
    {
        $versions = [
            '17.111.9',
            '1.2.3',
            '18.22.10',
            '18.33.10',
            '18.22.9'
        ];

        $this->assertEquals('1.2.3', $this->filter->oldest($versions));
    }

    public function testCanFilterByConstraint()
    {
        $versions = [
            '17.111.9',
            '1.2.3',
            '18.22.10',
            '18.33.10',
            '18.22.9',
            '19.1.0',
            '19.0.0'
        ];

        $resultVersions = [
            '18.22.10',
            '18.33.10',
            '18.22.9'
        ];

        $this->assertEquals($resultVersions, $this->filter->byConstraint('^18.0.0', $versions));

        $resultVersions = [
            '19.1.0',
            '19.0.0'
        ];

        $this->assertEquals($resultVersions, $this->filter->byConstraint('^19.0.0', $versions));
    }

    public function testCanGetLatestByConstraint()
    {
        $versions = [
            '17.111.9',
            '1.2.3',
            '18.22.10',
            '18.33.10',
            '18.22.9',
            '19.1.0',
            '19.0.0',
            '19.0.0-beta.1'
        ];

        $this->assertEquals('', $this->filter->latestByConstraint('^0.0.0', $versions));
        $this->assertEquals('', $this->filter->latestByConstraint('^2.0.0', $versions));
        $this->assertEquals('18.33.10', $this->filter->latestByConstraint('^18.0.0', $versions));
        $this->assertEquals('19.1.0', $this->filter->latestByConstraint('^19.0.0', $versions));
    }

    public function testCanGetOldestByConstraint()
    {
        $versions = [
            '17.111.9',
            '1.2.3',
            '18.22.10',
            '18.33.10',
            '18.22.9',
            '19.1.0',
            '19.0.0',
            '19.0.0-beta.1'
        ];

        $this->assertEquals('', $this->filter->oldestByConstraint('^0.0.0', $versions));
        $this->assertEquals('', $this->filter->oldestByConstraint('^2.0.0', $versions));
        $this->assertEquals('18.22.9', $this->filter->oldestByConstraint('^18.0.0', $versions));
        $this->assertEquals('19.0.0-beta.1', $this->filter->oldestByConstraint('^19.0.0-beta', $versions));
        $this->assertEquals('19.0.0', $this->filter->oldestByConstraint('^19.0.0', $versions));
    }

    public function testCanFilterStable()
    {
        $versions = [
            '18.33.10',
            '19.0.0-beta.1',
            '18.22.9',
            '19.1.0-alpha.1',
            '19.0.0'
        ];

        $resultVersions = [
            '18.33.10',
            '18.22.9',
            '19.0.0'
        ];

        $this->assertEquals($resultVersions, $this->filter->stable($versions));
    }
}
