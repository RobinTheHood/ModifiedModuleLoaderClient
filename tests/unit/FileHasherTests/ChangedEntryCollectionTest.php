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

namespace RobinTheHood\ModifiedModuleLoaderClient\Tests\Unit\FileHasherTests;

use PHPUnit\Framework\TestCase;
use RobinTheHood\ModifiedModuleLoaderClient\FileHasher\ChangedEntry;
use RobinTheHood\ModifiedModuleLoaderClient\FileHasher\ChangedEntryCollection;
use RobinTheHood\ModifiedModuleLoaderClient\FileHasher\HashEntry;

class ChangedEntryCollectionTest extends TestCase
{
    public function testCanConstruct()
    {
        $changeEntry = $this->createChangedEntry('/dir/testfile.php', ChangedEntry::TYPE_CHANGED);

        $changedEntryCollection = new ChangedEntryCollection([
            $changeEntry
        ]);

        $this->assertCount(1, $changedEntryCollection->changedEntries);
        $this->assertEquals($changeEntry, $changedEntryCollection->changedEntries[0]);
    }

    public function testGetByType()
    {
        $changeEntry1 = $this->createChangedEntry('/dir/testfile1.php', ChangedEntry::TYPE_CHANGED);
        $changeEntry2 = $this->createChangedEntry('/dir/testfile2.php', ChangedEntry::TYPE_NEW);

        $changedEntryCollection = new ChangedEntryCollection([$changeEntry1, $changeEntry2]);
        $new = $changedEntryCollection->getByType(ChangedEntry::TYPE_NEW);

        $this->assertEquals($changeEntry2, $new->changedEntries[0]);
    }

    // public function testCanCreateFromHashEntryCollection()
    // {
    //     $hashEntry = new HashEntry();
    //     $hashEntry->file = '/dir/testfile.php';

    //     $expectedChangeEntry = new ChangedEntry();
    //     $expectedChangeEntry->hashEntryA = $hashEntry;
    //     $expectedChangeEntry->type = ChangedEntry::TYPE_CHANGED;

    //     $hashEntry = new HashEntry();
    //     $hashEntry->file = '/dir/testfile.php';
    //     $hashEntry->hash = md5('code');

    //     $hashEntryCollection = new HashEntryCollection([$hashEntry]);

    //     $changedEntryCollection
    //         = ChangedEntryCollection::createFromHashEntryCollection(ChangedEntry::TYPE_CHANGED, $hashEntryCollection, null);

    //     $this->assertCount(1, $changedEntryCollection->changedEntries);
    //     $this->assertEquals($expectedChangeEntry, $changedEntryCollection->changedEntries[0]);
    // }

    public function testCanMerge()
    {
        $changeEntry1 = $this->createChangedEntry('/dir/testfile1.php', ChangedEntry::TYPE_CHANGED);
        $changeEntry2 = $this->createChangedEntry('/dir/testfile2.php', ChangedEntry::TYPE_NEW);

        $changedEntryCollection1 = new ChangedEntryCollection([$changeEntry1]);
        $changedEntryCollection2 = new ChangedEntryCollection([$changeEntry2]);

        $mergedChangedEntryCollection
            = ChangedEntryCollection::merge([$changedEntryCollection1, $changedEntryCollection2]);

        $this->assertCount(2, $mergedChangedEntryCollection->changedEntries);
        $this->assertEquals([$changeEntry1, $changeEntry2], $mergedChangedEntryCollection->changedEntries);
    }

    public function testAdd()
    {
        $changeEntry1 = $this->createChangedEntry('/dir/testfile1.php', ChangedEntry::TYPE_CHANGED);
        $changeEntry2 = $this->createChangedEntry('/dir/testfile2.php', ChangedEntry::TYPE_NEW);

        $changedEntryCollection = new ChangedEntryCollection([$changeEntry1]);
        $changedEntryCollection->add($changeEntry2);

        $this->assertCount(2, $changedEntryCollection->changedEntries);
        $this->assertEquals($changeEntry2, $changedEntryCollection->changedEntries[1]);
    }

    public function testUnique()
    {
        $changeEntry1 = $this->createChangedEntry('/dir/testfile1.php', ChangedEntry::TYPE_CHANGED);
        $changeEntry2 = $this->createChangedEntry('/dir/testfile1.php', ChangedEntry::TYPE_CHANGED);
        $changedEntryCollection = new ChangedEntryCollection([$changeEntry1, $changeEntry2]);
        $changedEntryCollection = $changedEntryCollection->unique();

        $this->assertCount(1, $changedEntryCollection->changedEntries);
    }

    private function createChangedEntry(string $file, int $type): ChangedEntry
    {
        $hashEntry = new HashEntry();
        $hashEntry->file = $file;

        $changeEntry = new ChangedEntry();
        $changeEntry->hashEntryA = $hashEntry;
        $changeEntry->type = $type;

        return $changeEntry;
    }
}
