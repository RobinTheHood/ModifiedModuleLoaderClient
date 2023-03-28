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
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\Combination;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\SystemSet;

class SystemSetTest extends TestCase
{
    public function testGetArchiveNamesReturnsExpectedArray()
    {
        // Arrange
        $systems = [
            'systemA' => '1.0',
            'systemB/1.2' => '1.2',
            'systemC/2.0' => '2.0',
            'systemD/2.1' => '2.1',
        ];
        $systemSet = new SystemSet();
        $systemSet->set($systems);

        // Act
        $result = $systemSet->getArchiveNames();

        // Assert
        $expected = ['systemB/1.2', 'systemC/2.0', 'systemD/2.1'];
        $this->assertEquals($expected, $result);
    }
}
