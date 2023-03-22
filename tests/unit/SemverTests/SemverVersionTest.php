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
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Version;

class SemverVersionTest extends TestCase
{
    public function testCanConvertToArray()
    {
        $version = new Version(1, 2, 3, 'beta.1');
        $this->assertEquals('1.2.3-beta.1', $version);

        $version = new Version(10, 3, 5, '');
        $this->assertEquals('10.3.5', $version);
    }

    public function testCanGetNextMinor()
    {
        $version = new Version(1, 2, 3, 'beta.1');
        $this->assertEquals('1.2.3-beta.1', $version);

        $nextMinorVersion = $version->nextMinor();
        $this->assertEquals('1.3.0', $nextMinorVersion);
    }
}
