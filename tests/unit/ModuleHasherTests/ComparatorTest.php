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
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\Comparator;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\HashEntry;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\HashEntryCollection;

class ComparatorTest extends TestCase
{
    public function testGetANotInB()
    {
        $hashEntry1 = $this->createHashEntry('/dir/testfile1.php', md5('code1'));
        $hashEntry2 = $this->createHashEntry('/dir/testfile2.php', md5('code2'));

        $hashEntryCollectionA = new HashEntryCollection([$hashEntry1, $hashEntry2]);
        $hashEntryCollectionB = new HashEntryCollection([$hashEntry1]);

        $comparator = new Comparator();
        $changedEntryCollection = $comparator->getANotInB($hashEntryCollectionA, $hashEntryCollectionB, ChangedEntry::TYPE_NEW);

        $this->assertEquals($hashEntry2, $changedEntryCollection->changedEntries[0]->hashEntryA);
    }

    public function testANotEqualToB()
    {
        $hashEntry1 = $this->createHashEntry('/dir/testfile1.php', md5('code1'));
        $hashEntry2 = $this->createHashEntry('/dir/testfile2.php', md5('code2'));
        $hashEntry3 = $this->createHashEntry('/dir/testfile2.php', md5('code3'));

        $hashEntryCollectionA = new HashEntryCollection([$hashEntry1, $hashEntry2]);
        $hashEntryCollectionB = new HashEntryCollection([$hashEntry1, $hashEntry3]);

        $comparator = new Comparator();
        $changedEntryCollection = $comparator->getANotEqualToB($hashEntryCollectionA, $hashEntryCollectionB, ChangedEntry::TYPE_NEW);

        $this->assertEquals($hashEntry2, $changedEntryCollection->changedEntries[0]->hashEntryA);
    }

    public function testGetChangedEntries()
    {
        $hashEntry1 = new HashEntry();
        $hashEntry1->file = '/dir/testfile1.php';
        $hashEntry1->hash = md5('code');

        $hashEntry2 = new HashEntry();
        $hashEntry2->file = '/dir/testfile2.php';
        $hashEntry2->hash = md5('code2');

        $hashEntry2Changed = new HashEntry();
        $hashEntry2Changed->file = '/dir/testfile2.php';
        $hashEntry2Changed->hash = md5('code3');

        $hashEntry4New = new HashEntry();
        $hashEntry4New->file = '/dir/testfile4.php';
        $hashEntry4New->hash = md5('code4');

        $installed = new HashEntryCollection([
            $hashEntry1,
            $hashEntry2
        ]);

        $shop = new HashEntryCollection([
            $hashEntry2Changed,
        ]);

        $mmlc = new HashEntryCollection([
            $hashEntry1,
            $hashEntry2,
            $hashEntry4New,
        ]);

        $comparator = new Comparator();
        $changedEntryCollection = $comparator->getChangedEntries($installed, $shop, $mmlc);

        $this->assertEquals('/dir/testfile4.php', $changedEntryCollection->changedEntries[0]->hashEntryA->file);
        $this->assertEquals(ChangedEntry::TYPE_NEW, $changedEntryCollection->changedEntries[0]->type);

        $this->assertEquals('/dir/testfile1.php', $changedEntryCollection->changedEntries[1]->hashEntryA->file);
        $this->assertEquals(ChangedEntry::TYPE_DELETED, $changedEntryCollection->changedEntries[1]->type);

        $this->assertEquals('/dir/testfile2.php', $changedEntryCollection->changedEntries[2]->hashEntryA->file);
        $this->assertEquals(ChangedEntry::TYPE_CHANGED, $changedEntryCollection->changedEntries[2]->type);
    }

    private function createHashEntry(string $file, $hash): HashEntry
    {
        $hashEntry = new HashEntry();
        $hashEntry->file = $file;
        $hashEntry->hash = $hash;
        return $hashEntry;
    }
}
