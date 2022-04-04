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

namespace RobinTheHood\ModifiedModuleLoaderClient\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;

class SemverParserTest extends TestCase
{
    protected function setUp(): void
    {
        $this->parser = new Parser();
    }

    public function testSemverCanParseVersionString()
    {
        $version = $this->parser->parse('1.2.3');
        $this->assertEquals(1, $version->getMajor());
        $this->assertEquals(2, $version->getMinor());
        $this->assertEquals(3, $version->getPatch());
    }

    public function testSemverCanParseVersionStringWithPrefix()
    {
        $version = $this->parser->parse('v11.222.3333');
        $this->assertEquals(11, $version->getMajor());
        $this->assertEquals(222, $version->getMinor());
        $this->assertEquals(3333, $version->getPatch());
    }

    public function testSemverCanParseVersionStringWithTag()
    {
        $version = $this->parser->parse('1.2.3-beta');
        $this->assertEquals(1, $version->getMajor());
        $this->assertEquals(2, $version->getMinor());
        $this->assertEquals(3, $version->getPatch());
        $this->assertEquals('beta', $version->getTag());
    }

    public function testSemverCanParseVersionStringWithPrefixAndTag()
    {
        $version = $this->parser->parse('v17.7.87-alpha');
        $this->assertEquals(17, $version->getMajor());
        $this->assertEquals(7, $version->getMinor());
        $this->assertEquals(87, $version->getPatch());
        $this->assertEquals('alpha', $version->getTag());
    }

    public function testCanCheckIfStringAVersion()
    {
        $this->assertFalse($this->parser->isVersion('a1.2.3'));
        $this->assertFalse($this->parser->isVersion('test'));
        $this->assertFalse($this->parser->isVersion('1.2.'));
        $this->assertFalse($this->parser->isVersion('^1.2.3'));

        $this->assertTrue($this->parser->isVersion('1.2.3'));
        $this->assertTrue($this->parser->isVersion('99.2.3'));
        $this->assertTrue($this->parser->isVersion('1.99.3'));
        $this->assertTrue($this->parser->isVersion('1.2.99'));
        $this->assertTrue($this->parser->isVersion('99.99.99'));
    }

    public function testSemverThrowsParseErrorException1()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\Semver\ParseErrorException::class);
        $version = $this->parser->parse('.');
    }

    public function testSemverThrowsParseErrorException2()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\Semver\ParseErrorException::class);
        $version = $this->parser->parse('..');
    }

    public function testSemverThrowsParseErrorException3()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\Semver\ParseErrorException::class);
        $version = $this->parser->parse('...');
    }

    public function testSemverThrowsParseErrorException4()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\Semver\ParseErrorException::class);
        $version = $this->parser->parse('1');
    }

    public function testSemverThrowsParseErrorException5()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\Semver\ParseErrorException::class);
        $version = $this->parser->parse('1.');
    }

    public function testSemverThrowsParseErrorException6()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\Semver\ParseErrorException::class);
        $version = $this->parser->parse('1.2');
    }

    public function testSemverThrowsParseErrorException7()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\Semver\ParseErrorException::class);
        $version = $this->parser->parse('1.2.');
    }

    public function testSemverThrowsParseErrorException8()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\Semver\ParseErrorException::class);
        $version = $this->parser->parse('1.2.3.');
    }

    public function testSemverThrowsParseErrorException9()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\Semver\ParseErrorException::class);
        $version = $this->parser->parse('1.2.3-');
    }
}
