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
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\ChangedEntryCollection;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\HashEntry;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\HashEntryCollection;

class ChangedEntryCollectionTest extends TestCase
{
    public function testCanConstruct()
    {
        $changeEntry = new ChangedEntry();
        $changeEntry->file = '/dir/testfile.php';
        $changeEntry->type = ChangedEntry::TYPE_CHANGED;

        $changedEntryCollection = new ChangedEntryCollection([
            $changeEntry
        ]);

        $this->assertCount(1, $changedEntryCollection->changedEntries);
        $this->assertEquals($changeEntry, $changedEntryCollection->changedEntries[0]);
    }

    public function testGetByType()
    {
        $changeEntry1 = new ChangedEntry();
        $changeEntry1->file = '/dir/testfile.php';
        $changeEntry1->type = ChangedEntry::TYPE_CHANGED;

        $changeEntry2 = new ChangedEntry();
        $changeEntry2->file = '/dir/testfile2.php';
        $changeEntry2->type = ChangedEntry::TYPE_NEW;

        $changedEntryCollection = new ChangedEntryCollection([$changeEntry1, $changeEntry2]);
        $new = $changedEntryCollection->getByType(ChangedEntry::TYPE_NEW);

        $this->assertEquals($changeEntry2, $new->changedEntries[0]);
    }

    public function testCanCreateFromHashEntryCollection()
    {
        $expectedChangeEntry = new ChangedEntry();
        $expectedChangeEntry->file = '/dir/testfile.php';
        $expectedChangeEntry->type = ChangedEntry::TYPE_CHANGED;

        $hashEntry = new HashEntry();
        $hashEntry->file = '/dir/testfile.php';
        $hashEntry->hash = md5('code');

        $hashEntryCollection = new HashEntryCollection([$hashEntry]);

        $changedEntryCollection
            = ChangedEntryCollection::createFromHashEntryCollection($hashEntryCollection, ChangedEntry::TYPE_CHANGED);

        $this->assertCount(1, $changedEntryCollection->changedEntries);
        $this->assertEquals($expectedChangeEntry, $changedEntryCollection->changedEntries[0]);
    }

    public function testCanMerge()
    {
        $changeEntry1 = new ChangedEntry();
        $changeEntry1->file = '/dir/testfile.php';
        $changeEntry1->type = ChangedEntry::TYPE_CHANGED;

        $changeEntry2 = new ChangedEntry();
        $changeEntry2->file = '/dir/testfile2.php';
        $changeEntry2->type = ChangedEntry::TYPE_NEW;

        $changedEntryCollection1 = new ChangedEntryCollection([$changeEntry1]);
        $changedEntryCollection2 = new ChangedEntryCollection([$changeEntry2]);

        $mergedChangedEntryCollection
            = ChangedEntryCollection::merge([$changedEntryCollection1, $changedEntryCollection2]);

        $this->assertCount(2, $mergedChangedEntryCollection->changedEntries);
        $this->assertEquals([$changeEntry1, $changeEntry2], $mergedChangedEntryCollection->changedEntries);
    }
}
