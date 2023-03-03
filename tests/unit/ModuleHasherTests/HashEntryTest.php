<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace RobinTheHood\ModifiedModuleLoaderClient\Tests\Unit\ModuleHahserTests;

use PHPUnit\Framework\TestCase;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\HashEntry;

class HashEntryTest extends TestCase
{
    public function testClone()
    {
        $hashEntry = new HashEntry();
        $hashEntry->file = '/dir/testfile.php';
        $hashEntry->hash = md5('code');

        $clonedHashEntry = $hashEntry->clone();
        $this->assertEquals($hashEntry, $clonedHashEntry);
    }
}
