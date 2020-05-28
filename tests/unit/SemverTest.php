<?php

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

class SemverTest extends TestCase
{
    public function testSemverCanParseVersionString()
    {
        $version = Semver::Parse('1.2.3');

        $expectedVersion = [
            'major' => '1',
            'minor' => '2',
            'patch' => '3'
        ];

        $this->assertEquals($expectedVersion, $version);
    }

    public function testSemverThrowsParseErrorException1()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\ParseErrorException::class);
        $version = Semver::Parse('.');
    }

    public function testSemverThrowsParseErrorException2()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\ParseErrorException::class);
        $version = Semver::Parse('..');
    }

    public function testSemverThrowsParseErrorException3()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\ParseErrorException::class);
        $version = Semver::Parse('...');
    }

    public function testSemverThrowsParseErrorException4()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\ParseErrorException::class);
        $version = Semver::Parse('1');
    }

    public function testSemverThrowsParseErrorException5()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\ParseErrorException::class);
        $version = Semver::Parse('1.');
    }

    public function testSemverThrowsParseErrorException6()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\ParseErrorException::class);
        $version = Semver::Parse('1.2');
    }

    public function testSemverThrowsParseErrorException7()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\ParseErrorException::class);
        $version = Semver::Parse('1.2.');
    }

    public function testSemverThrowsParseErrorException8()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\ParseErrorException::class);
        $version = Semver::Parse('1.2.3.');
    }

    public function testSemverCanHandleGreaterThan()
    {
        $this->assertTrue(Semver::greaterThan('auto', '1.2.3'));
        $this->assertFalse(Semver::greaterThan('1.2.3', 'auto'));

        $this->assertFalse(Semver::greaterThan('1.2.3', '1.2.3'));
        $this->assertFalse(Semver::greaterThan('2.2.9', '2.2.10'));
        $this->assertTrue(Semver::greaterThan('1.2.10', '1.2.9'));
        $this->assertTrue(Semver::greaterThan('1.3.3', '1.2.3'));
        $this->assertTrue(Semver::greaterThan('2.2.3', '1.2.3'));
        
    }

    public function testSemverCanHandleEqualTo()
    {
        $this->assertTrue(Semver::equalTo('auto', 'auto'));
        $this->assertFalse(Semver::equalTo('auto', '1.2.3'));
        $this->assertFalse(Semver::equalTo('1.2.3', 'auto'));

        $this->assertFalse(Semver::equalTo('1.2.3', '1.2.4'));
        $this->assertFalse(Semver::equalTo('1.2.4', '1.2.3'));
        $this->assertTrue(Semver::equalTo('1.2.3', '1.2.3'));
    }


    public function testSemverCanHandleGreaterThanOrEqualTo()
    {
        $this->assertTrue(Semver::greaterThanOrEqualTo('1.2.3', '1.2.3'));
        $this->assertTrue(Semver::greaterThanOrEqualTo('1.2.4', '1.2.3'));
        $this->assertTrue(Semver::greaterThanOrEqualTo('1.3.3', '1.2.3'));
        $this->assertTrue(Semver::greaterThanOrEqualTo('2.2.3', '1.2.3'));
        $this->assertFalse(Semver::greaterThanOrEqualTo('2.2.3', '2.2.4'));
    }

    public function testSemverCanHandleLessThan()
    {
        $this->assertFalse(Semver::lessThan('auto', '1.2.3'));
        $this->assertTrue(Semver::lessThan('1.2.3', 'auto'));

        $this->assertFalse(Semver::lessThan('1.2.3', '1.2.3'));
        $this->assertFalse(Semver::lessThan('1.2.10', '1.2.9'));
        $this->assertFalse(Semver::lessThan('1.10.3', '1.9.3'));
        $this->assertFalse(Semver::lessThan('10.2.3', '9.2.3'));
        $this->assertTrue(Semver::lessThan('2.2.9', '2.2.10'));
    }

    public function testSemverCanHandleLessThanOrEqualTo()
    {
        $this->assertTrue(Semver::lessThanOrEqualTo('1.2.3', '1.2.3'));
        $this->assertTrue(Semver::lessThanOrEqualTo('2.2.9', '2.2.10'));
        $this->assertFalse(Semver::lessThanOrEqualTo('1.2.10', '1.2.9'));
        $this->assertFalse(Semver::lessThanOrEqualTo('1.10.3', '1.9.3'));
        $this->assertFalse(Semver::lessThanOrEqualTo('10.2.3', '9.2.3'));
       
    }

    public function testSemverCanHandleNotEqualTo()
    {
        $this->assertTrue(Semver::notEqualTo('1.2.3', '1.2.4'));
        $this->assertTrue(Semver::notEqualTo('1.2.4', '1.2.3'));
        $this->assertFalse(Semver::notEqualTo('1.2.3', '1.2.3'));
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

        $this->assertEquals($expected, Semver::sort($versions));
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

        $this->assertEquals($expected, Semver::rsort($versions));
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

        $this->assertEquals('18.33.10', Semver::highest($versions));
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

        $this->assertEquals('1.2.3', Semver::lowest($versions));
    }

    public function testThatVersionAIsCompatibleWithVersionB()
    {
        $this->assertTrue(Semver::isCompatible('auto', '3.3.3'));

        $this->assertTrue(Semver::isCompatible('3.3.3', '3.3.3'));
        $this->assertTrue(Semver::isCompatible('3.3.3', '3.3.2'));
        $this->assertTrue(Semver::isCompatible('3.3.3', '3.2.4'));

        $this->assertFalse(Semver::isCompatible('3.3.3', '2.3.3'));
        $this->assertFalse(Semver::isCompatible('3.3.3', '3.4.3'));
        $this->assertFalse(Semver::isCompatible('3.3.3', '3.3.4'));
    }

    public function testThatVersionASatisfiesContraint()
    {
        $this->assertTrue(Semver::satisfies('3.3.3', '^3.3.3'));
        $this->assertTrue(Semver::satisfies('3.3.3', '^3.2.3'));
        $this->assertTrue(Semver::satisfies('3.3.3', '3.3.3'));
        $this->assertFalse(Semver::satisfies('3.3.3', '3.2.3'));
    }
}