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
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\ChangedEntry;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\HashEntry;

class ChangedEntryTest extends TestCase
{
    public function testCanCreateFromHashEntry1()
    {
        $hashEntry = new HashEntry();
        $hashEntry->file = '/dir/testfile.php';
        $hashEntry->hash = md5('code');

        $expectedChangedEntry = new ChangedEntry();
        $expectedChangedEntry->file = '/dir/testfile.php';
        $expectedChangedEntry->type = ChangedEntry::TYPE_CHANGED;

        $changedEntry = ChangedEntry::createFromHashEntry($hashEntry, ChangedEntry::TYPE_CHANGED);

        $this->assertEquals($expectedChangedEntry, $changedEntry);
    }

    public function testCanCreateFromHashEntry2()
    {
        $hashEntry = new HashEntry();
        $hashEntry->file = '/dir/testfile.php';
        $hashEntry->hash = md5('code');

        $expectedChangedEntry = new ChangedEntry();
        $expectedChangedEntry->file = '/dir/testfile.php';
        $expectedChangedEntry->type = ChangedEntry::TYPE_NEW;

        $changedEntry = ChangedEntry::createFromHashEntry($hashEntry, ChangedEntry::TYPE_NEW);

        $this->assertEquals($expectedChangedEntry, $changedEntry);
    }
}
