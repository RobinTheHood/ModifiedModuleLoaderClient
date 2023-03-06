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
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\DependencyBuilder;

class DependencyBuilderTest extends TestCase
{
    public function testInvokeDependency()
    {
        $dpb = new DependencyBuilder();
        $dpb->test();
        die('TEST DONE');
    }
}
