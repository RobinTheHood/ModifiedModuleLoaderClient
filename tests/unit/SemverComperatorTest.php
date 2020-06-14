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

use PHPUnit\Framework\TestCase;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;

class SemverComparatorTest extends TestCase
{
    public $comparator;

    public function setUp()
    {
        $this->comparator = new Comparator(new Parser());
    }

    public function testSemverCanHandleGreaterThan()
    {
        $this->assertTrue($this->comparator->greaterThan('auto', '1.2.3'));
        $this->assertFalse($this->comparator->greaterThan('1.2.3', 'auto'));

        $this->assertFalse($this->comparator->greaterThan('1.2.3', '1.2.3'));
        $this->assertFalse($this->comparator->greaterThan('2.2.9', '2.2.10'));
        $this->assertTrue($this->comparator->greaterThan('1.2.10', '1.2.9'));
        $this->assertTrue($this->comparator->greaterThan('1.3.3', '1.2.3'));
        $this->assertTrue($this->comparator->greaterThan('2.2.3', '1.2.3'));
        
        $this->assertTrue($this->comparator->greaterThan('v1.2.10', 'v1.2.9'));
        $this->assertTrue($this->comparator->greaterThan('v1.3.3', '1.2.3'));
        $this->assertTrue($this->comparator->greaterThan('2.2.3', 'v1.2.3'));

        $this->assertTrue($this->comparator->greaterThan('1.2.3', '1.2.3-beta'));
        $this->assertTrue($this->comparator->greaterThan('1.2.3-beta', 'v1.2.3-alpha'));
        $this->assertTrue($this->comparator->greaterThan('1.2.3-rc.1', 'v1.2.3-beta'));

        $this->assertFalse($this->comparator->greaterThan('1.2.3-beta', 'v1.2.3'));
    }

    public function testSemverCanHandleEqualTo()
    {
        $this->assertTrue($this->comparator->equalTo('auto', 'auto'));
        $this->assertFalse($this->comparator->equalTo('auto', '1.2.3'));
        $this->assertFalse($this->comparator->equalTo('1.2.3', 'auto'));

        $this->assertFalse($this->comparator->equalTo('1.2.3', '1.2.4'));
        $this->assertFalse($this->comparator->equalTo('1.2.4', '1.2.3'));
        $this->assertTrue($this->comparator->equalTo('1.2.3', '1.2.3'));

        $this->assertTrue($this->comparator->equalTo('v1.2.3', 'v1.2.3'));
        $this->assertTrue($this->comparator->equalTo('1.2.3', 'v1.2.3'));
        $this->assertTrue($this->comparator->equalTo('v1.2.3', '1.2.3'));

        $this->assertTrue($this->comparator->equalTo('v1.2.3-alpha', '1.2.3-alpha'));
        $this->assertTrue($this->comparator->equalTo('1.2.3-beta', '1.2.3-beta'));
        $this->assertTrue($this->comparator->equalTo('1.2.3-rc.1', 'v1.2.3-rc.1'));
    }


    public function testSemverCanHandleGreaterThanOrEqualTo()
    {
        $this->assertTrue($this->comparator->greaterThanOrEqualTo('1.2.3', '1.2.3'));
        $this->assertTrue($this->comparator->greaterThanOrEqualTo('1.2.4', '1.2.3'));
        $this->assertTrue($this->comparator->greaterThanOrEqualTo('1.3.3', '1.2.3'));
        $this->assertTrue($this->comparator->greaterThanOrEqualTo('2.2.3', '1.2.3'));
        $this->assertFalse($this->comparator->greaterThanOrEqualTo('2.2.3', '2.2.4'));
    }

    public function testSemverCanHandleLessThan()
    {
        $this->assertFalse($this->comparator->lessThan('auto', '1.2.3'));
        $this->assertTrue($this->comparator->lessThan('1.2.3', 'auto'));

        $this->assertFalse($this->comparator->lessThan('1.2.3', '1.2.3'));
        $this->assertFalse($this->comparator->lessThan('1.2.10', '1.2.9'));
        $this->assertFalse($this->comparator->lessThan('1.10.3', '1.9.3'));
        $this->assertFalse($this->comparator->lessThan('10.2.3', '9.2.3'));
        $this->assertTrue($this->comparator->lessThan('2.2.9', '2.2.10'));

        $this->assertTrue($this->comparator->lessThan('2.2.9-beta', '2.2.9'));
    }

    public function testSemverCanHandleLessThanOrEqualTo()
    {
        $this->assertTrue($this->comparator->lessThanOrEqualTo('1.2.3', '1.2.3'));
        $this->assertTrue($this->comparator->lessThanOrEqualTo('2.2.9', '2.2.10'));
        $this->assertFalse($this->comparator->lessThanOrEqualTo('1.2.10', '1.2.9'));
        $this->assertFalse($this->comparator->lessThanOrEqualTo('1.10.3', '1.9.3'));
        $this->assertFalse($this->comparator->lessThanOrEqualTo('10.2.3', '9.2.3'));
       
    }

    public function testSemverCanHandleNotEqualTo()
    {
        $this->assertTrue($this->comparator->notEqualTo('1.2.3', '1.2.4'));
        $this->assertTrue($this->comparator->notEqualTo('1.2.4', '1.2.3'));
        $this->assertFalse($this->comparator->notEqualTo('1.2.3', '1.2.3'));
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

        $this->assertEquals($expected, $this->comparator->sort($versions));
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

        $this->assertEquals($expected, $this->comparator->rsort($versions));
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

        $this->assertEquals('18.33.10', $this->comparator->highest($versions));
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

        $this->assertEquals('1.2.3', $this->comparator->lowest($versions));
    }

    public function testThatVersionAIsCompatibleWithVersionB()
    {
        $this->assertTrue($this->comparator->isCompatible('auto', '3.3.3'));

        $this->assertTrue($this->comparator->isCompatible('3.3.3', '3.3.3'));
        $this->assertTrue($this->comparator->isCompatible('3.3.3', '3.3.2'));
        $this->assertTrue($this->comparator->isCompatible('3.3.3', '3.2.4'));

        $this->assertFalse($this->comparator->isCompatible('3.3.3', '2.3.3'));
        $this->assertFalse($this->comparator->isCompatible('3.3.3', '3.4.3'));
        $this->assertFalse($this->comparator->isCompatible('3.3.3', '3.3.4'));
    }

    public function testThatVersionASatisfiesContraint()
    {
        $this->assertTrue($this->comparator->satisfies('3.3.3', '^3.3.3'));
        $this->assertTrue($this->comparator->satisfies('3.3.3', '^3.2.3'));
        $this->assertTrue($this->comparator->satisfies('3.3.3', '3.3.3'));
        $this->assertFalse($this->comparator->satisfies('3.3.3', '3.2.3'));
    }
}