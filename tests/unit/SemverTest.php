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
use RobinTheHood\ModifiedModuleLoaderClient\Semver;
use RobinTheHood\ModifiedModuleLoaderClient\SemverParser;

class SemverTest extends TestCase
{
    public $semver;

    public function setUp()
    {
        $this->semver = new Semver(new SemverParser);
    }

    public function testSemverCanHandleGreaterThan()
    {
        $this->assertTrue($this->semver->greaterThan('auto', '1.2.3'));
        $this->assertFalse($this->semver->greaterThan('1.2.3', 'auto'));

        $this->assertFalse($this->semver->greaterThan('1.2.3', '1.2.3'));
        $this->assertFalse($this->semver->greaterThan('2.2.9', '2.2.10'));
        $this->assertTrue($this->semver->greaterThan('1.2.10', '1.2.9'));
        $this->assertTrue($this->semver->greaterThan('1.3.3', '1.2.3'));
        $this->assertTrue($this->semver->greaterThan('2.2.3', '1.2.3'));
        
    }

    public function testSemverCanHandleEqualTo()
    {
        $this->assertTrue($this->semver->equalTo('auto', 'auto'));
        $this->assertFalse($this->semver->equalTo('auto', '1.2.3'));
        $this->assertFalse($this->semver->equalTo('1.2.3', 'auto'));

        $this->assertFalse($this->semver->equalTo('1.2.3', '1.2.4'));
        $this->assertFalse($this->semver->equalTo('1.2.4', '1.2.3'));
        $this->assertTrue($this->semver->equalTo('1.2.3', '1.2.3'));
    }


    public function testSemverCanHandleGreaterThanOrEqualTo()
    {
        $this->assertTrue($this->semver->greaterThanOrEqualTo('1.2.3', '1.2.3'));
        $this->assertTrue($this->semver->greaterThanOrEqualTo('1.2.4', '1.2.3'));
        $this->assertTrue($this->semver->greaterThanOrEqualTo('1.3.3', '1.2.3'));
        $this->assertTrue($this->semver->greaterThanOrEqualTo('2.2.3', '1.2.3'));
        $this->assertFalse($this->semver->greaterThanOrEqualTo('2.2.3', '2.2.4'));
    }

    public function testSemverCanHandleLessThan()
    {
        $this->assertFalse($this->semver->lessThan('auto', '1.2.3'));
        $this->assertTrue($this->semver->lessThan('1.2.3', 'auto'));

        $this->assertFalse($this->semver->lessThan('1.2.3', '1.2.3'));
        $this->assertFalse($this->semver->lessThan('1.2.10', '1.2.9'));
        $this->assertFalse($this->semver->lessThan('1.10.3', '1.9.3'));
        $this->assertFalse($this->semver->lessThan('10.2.3', '9.2.3'));
        $this->assertTrue($this->semver->lessThan('2.2.9', '2.2.10'));
    }

    public function testSemverCanHandleLessThanOrEqualTo()
    {
        $this->assertTrue($this->semver->lessThanOrEqualTo('1.2.3', '1.2.3'));
        $this->assertTrue($this->semver->lessThanOrEqualTo('2.2.9', '2.2.10'));
        $this->assertFalse($this->semver->lessThanOrEqualTo('1.2.10', '1.2.9'));
        $this->assertFalse($this->semver->lessThanOrEqualTo('1.10.3', '1.9.3'));
        $this->assertFalse($this->semver->lessThanOrEqualTo('10.2.3', '9.2.3'));
       
    }

    public function testSemverCanHandleNotEqualTo()
    {
        $this->assertTrue($this->semver->notEqualTo('1.2.3', '1.2.4'));
        $this->assertTrue($this->semver->notEqualTo('1.2.4', '1.2.3'));
        $this->assertFalse($this->semver->notEqualTo('1.2.3', '1.2.3'));
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

        $this->assertEquals($expected, $this->semver->sort($versions));
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

        $this->assertEquals($expected, $this->semver->rsort($versions));
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

        $this->assertEquals('18.33.10', $this->semver->highest($versions));
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

        $this->assertEquals('1.2.3', $this->semver->lowest($versions));
    }

    public function testThatVersionAIsCompatibleWithVersionB()
    {
        $this->assertTrue($this->semver->isCompatible('auto', '3.3.3'));

        $this->assertTrue($this->semver->isCompatible('3.3.3', '3.3.3'));
        $this->assertTrue($this->semver->isCompatible('3.3.3', '3.3.2'));
        $this->assertTrue($this->semver->isCompatible('3.3.3', '3.2.4'));

        $this->assertFalse($this->semver->isCompatible('3.3.3', '2.3.3'));
        $this->assertFalse($this->semver->isCompatible('3.3.3', '3.4.3'));
        $this->assertFalse($this->semver->isCompatible('3.3.3', '3.3.4'));
    }

    public function testThatVersionASatisfiesContraint()
    {
        $this->assertTrue($this->semver->satisfies('3.3.3', '^3.3.3'));
        $this->assertTrue($this->semver->satisfies('3.3.3', '^3.2.3'));
        $this->assertTrue($this->semver->satisfies('3.3.3', '3.3.3'));
        $this->assertFalse($this->semver->satisfies('3.3.3', '3.2.3'));
    }
}