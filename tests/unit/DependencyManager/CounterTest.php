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

use PHPUnit\Framework\TestCase;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\Counter;

class CounterTest extends TestCase
{
    public function testCurrent()
    {
        $counter = new Counter([
            2, 1, 3
        ]);

        $this->assertEquals([0, 0, 0], $counter->current());
    }

    public function testNext()
    {
        $counter = new Counter([
            2, 1, 3
        ]);

        $this->assertEquals([0, 0, 1], $counter->next());
        $this->assertEquals([0, 0, 2], $counter->next());
        $this->assertEquals([0, 0, 3], $counter->next());
        $this->assertEquals([0, 1, 0], $counter->next());
        $this->assertEquals([0, 1, 1], $counter->next());
        $this->assertEquals([0, 1, 2], $counter->next());
        $this->assertEquals([0, 1, 3], $counter->next());
        $this->assertEquals([1, 0, 0], $counter->next());
        $this->assertEquals([1, 0, 1], $counter->next());
        $this->assertEquals([1, 0, 2], $counter->next());
        $this->assertEquals([1, 0, 3], $counter->next());
        $this->assertEquals([1, 1, 0], $counter->next());
        $this->assertEquals([1, 1, 1], $counter->next());
        $this->assertEquals([1, 1, 2], $counter->next());
        $this->assertEquals([1, 1, 3], $counter->next());
        $this->assertEquals([2, 0, 0], $counter->next());
        $this->assertEquals([2, 0, 1], $counter->next());
        $this->assertEquals([2, 0, 2], $counter->next());
        $this->assertEquals([2, 0, 3], $counter->next());
        $this->assertEquals([2, 1, 0], $counter->next());
        $this->assertEquals([2, 1, 1], $counter->next());
        $this->assertEquals([2, 1, 2], $counter->next());
        $this->assertEquals([2, 1, 3], $counter->next());
        $this->assertEquals([0, 0, 0], $counter->next());
    }

    public function testStart()
    {
        $counter = new Counter([
            2, 1
        ]);

        $this->assertEquals(true, $counter->isStart());
        $this->assertEquals([0, 0], $counter->current());
        $this->assertEquals([0, 1], $counter->next());
        $this->assertEquals(false, $counter->isStart());
        $this->assertEquals([1, 0], $counter->next());
        $this->assertEquals([1, 1], $counter->next());
        $this->assertEquals([2, 0], $counter->next());
        $this->assertEquals([2, 1], $counter->next());
        $this->assertEquals([0, 0], $counter->next());
        $this->assertEquals(true, $counter->isStart());
    }
}
