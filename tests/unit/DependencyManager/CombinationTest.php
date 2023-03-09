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

namespace RobinTheHood\ModifiedModuleLoaderClient\Tests\Unit\DependencyManager;

use Exception;
use PHPUnit\Framework\TestCase;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\Combination;

class CombinationTest extends TestCase
{

    public function testAdd()
    {
        $combination = new Combination();
        $combination->add('foo/bar', '1.0.0');

        $this->assertEquals(['foo/bar' => '1.0.0'], $combination->combinations);
    }

    public function testCanNotAddTwice()
    {
        $this->expectException(Exception::class);

        $combination = new Combination();
        $combination->add('foo/bar', '1.0.0');
        $combination->add('foo/bar', '2.0.0');
    }

    public function testOverwrite()
    {
        $combination = new Combination();
        $combination->add('foo/bar', '1.0.0');
        $combination->overwrite('foo/bar', '2.0.0');

        $this->assertEquals(['foo/bar' => '2.0.0'], $combination->combinations);
    }

    public function testGetVersion()
    {
        $combination = new Combination();
        $combination->add('foo', '1.0.0');
        $combination->add('foo/bar', '2.0.0');

        $this->assertEquals('2.0.0', $combination->getVersion('foo/bar'));
    }

    public function testGetVersionThrowsException()
    {
        $this->expectException(Exception::class);

        $combination = new Combination();
        $combination->add('foo', '1.0.0');
        $combination->add('foo/bar', '2.0.0');

        $combination->getVersion('bar');
    }

    public function testStrip()
    {
        $combination = new Combination();
        $combination->add('foo', '1.0.0');
        $combination->add('foo/bar', '2.0.0');
        $stripedCombination = $combination->strip();

        $this->assertEquals(
            [
                'foo' => '1.0.0',
                'foo/bar' => '2.0.0'
            ],
            $combination->combinations
        );

        $this->assertEquals(
            [
                'foo/bar' => '2.0.0'
            ],
            $stripedCombination->combinations
        );
    }
}
