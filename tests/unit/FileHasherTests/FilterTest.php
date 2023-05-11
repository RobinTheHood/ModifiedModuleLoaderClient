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
use RobinTheHood\ModifiedModuleLoaderClient\FileHasher\Filter;
use RobinTheHood\ModifiedModuleLoaderClient\FileHasher\HashEntry;
use RobinTheHood\ModifiedModuleLoaderClient\FileHasher\HashEntryCollection;

class FilterTest extends TestCase
{
    public function testGetANotInB()
    {
        $hashEntry1 = $this->createHashEntry('/dir/testfile1.php', md5('code1'));
        $hashEntry2 = $this->createHashEntry('/dir/testfile2.php', md5('code2'));

        $hashEntryCollectionA = new HashEntryCollection([$hashEntry1, $hashEntry2]);
        $hashEntryCollectionB = new HashEntryCollection([$hashEntry1]);

        $filter = new Filter();
        $changedEntryCollection = $filter->getANotInB($hashEntryCollectionA, $hashEntryCollectionB, ChangedEntry::TYPE_NEW);

        $this->assertEquals($hashEntry2, $changedEntryCollection->changedEntries[0]->hashEntryA);
    }

    public function testANotEqualToB()
    {
        $hashEntry1 = $this->createHashEntry('/dir/testfile1.php', md5('code1'));
        $hashEntry2 = $this->createHashEntry('/dir/testfile2.php', md5('code2'));
        $hashEntry3 = $this->createHashEntry('/dir/testfile2.php', md5('code3'));

        $hashEntryCollectionA = new HashEntryCollection([$hashEntry1, $hashEntry2]);
        $hashEntryCollectionB = new HashEntryCollection([$hashEntry1, $hashEntry3]);

        $filter = new Filter();
        $changedEntryCollection = $filter->getANotEqualToB($hashEntryCollectionA, $hashEntryCollectionB, ChangedEntry::TYPE_NEW);

        $this->assertEquals($hashEntry2, $changedEntryCollection->changedEntries[0]->hashEntryA);
    }

    private function createHashEntry(string $file, $hash): HashEntry
    {
        $hashEntry = new HashEntry();
        $hashEntry->file = $file;
        $hashEntry->hash = $hash;
        return $hashEntry;
    }
}
