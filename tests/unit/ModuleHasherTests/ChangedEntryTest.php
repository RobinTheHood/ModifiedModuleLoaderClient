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
    public function testCreateFromHashEntry1()
    {
        $hashEntry = new HashEntry();
        $hashEntry->file = '/dir/testfile.php';
        $hashEntry->hash = md5('code');

        $expectedChangedEntry = new ChangedEntry();
        $expectedChangedEntry->hashEntryA = $hashEntry;
        $expectedChangedEntry->type = ChangedEntry::TYPE_CHANGED;

        $changedEntry = ChangedEntry::createFromHashEntry(ChangedEntry::TYPE_CHANGED, $hashEntry, null);

        $this->assertEquals($expectedChangedEntry, $changedEntry);
    }

    public function testCreateFromHashEntry2()
    {
        $hashEntry = new HashEntry();
        $hashEntry->file = '/dir/testfile.php';
        $hashEntry->hash = md5('code');

        $expectedChangedEntry = new ChangedEntry();
        $expectedChangedEntry->hashEntryA = $hashEntry;
        $expectedChangedEntry->type = ChangedEntry::TYPE_NEW;

        $changedEntry = ChangedEntry::createFromHashEntry(ChangedEntry::TYPE_NEW, $hashEntry, null);

        $this->assertEquals($expectedChangedEntry, $changedEntry);
    }

    public function testClone()
    {
        $hashEntry = new HashEntry();
        $hashEntry->file = '/dir/testfile.php';
        $hashEntry->hash = md5('code');

        $changedEntry = new ChangedEntry();
        $changedEntry->hashEntryA = $hashEntry;
        $changedEntry->type = ChangedEntry::TYPE_NEW;

        $clonedChangedEntry = $changedEntry->clone();

        $this->assertEquals(ChangedEntry::TYPE_NEW, $clonedChangedEntry->type);
        $this->assertEquals($hashEntry, $clonedChangedEntry->hashEntryA);
    }
}
