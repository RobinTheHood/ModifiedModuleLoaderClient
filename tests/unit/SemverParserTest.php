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
use RobinTheHood\ModifiedModuleLoaderClient\SemverParser;

class SemverParserTest extends TestCase
{
    public function setUp()
    {
        $this->parser = new SemverParser();
    }

    public function testSemverCanParseVersionString()
    {
        $version = $this->parser->parse('1.2.3');

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
        $version = $this->parser->parse('.');
    }

    public function testSemverThrowsParseErrorException2()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\ParseErrorException::class);
        $version = $this->parser->parse('..');
    }

    public function testSemverThrowsParseErrorException3()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\ParseErrorException::class);
        $version = $this->parser->parse('...');
    }

    public function testSemverThrowsParseErrorException4()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\ParseErrorException::class);
        $version = $this->parser->parse('1');
    }

    public function testSemverThrowsParseErrorException5()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\ParseErrorException::class);
        $version = $this->parser->parse('1.');
    }

    public function testSemverThrowsParseErrorException6()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\ParseErrorException::class);
        $version = $this->parser->parse('1.2');
    }

    public function testSemverThrowsParseErrorException7()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\ParseErrorException::class);
        $version = $this->parser->parse('1.2.');
    }

    public function testSemverThrowsParseErrorException8()
    {
        $this->expectException(\RobinTheHood\ModifiedModuleLoaderClient\ParseErrorException::class);
        $version = $this->parser->parse('1.2.3.');
    }
}