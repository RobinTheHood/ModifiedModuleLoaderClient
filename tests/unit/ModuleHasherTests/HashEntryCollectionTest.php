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

namespace RobinTheHood\ModifiedModuleLoaderClient\Tests\Unit\ModuleHahserTests;

use PHPUnit\Framework\TestCase;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\HashEntry;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\HashEntryCollection;

class HashEntryCollectionTest extends TestCase
{
    public function testCanConstruct()
    {
        $hashEntry = new HashEntry();
        $hashEntry->file = '/dir/testfile.php';
        $hashEntry->hash = md5('code');

        $hashEntryCollection = new HashEntryCollection([
            $hashEntry
        ]);

        $this->assertCount(1, $hashEntryCollection->hashEntries);
        $this->assertEquals($hashEntry, $hashEntryCollection->hashEntries[0]);
    }

    public function testGetByFile()
    {
        $hashEntry1 = new HashEntry();
        $hashEntry1->file = '/dir/testfile.php';
        $hashEntry1->hash = md5('code');

        $hashEntry2 = new HashEntry();
        $hashEntry2->file = '/dir/testfile2.php';
        $hashEntry2->hash = md5('code');

        $hashEntryCollection = new HashEntryCollection([
            $hashEntry1,
            $hashEntry2
        ]);

        $hashEntry = $hashEntryCollection->getByFile('/dir/testfile2.php');

        $this->assertEquals($hashEntry2, $hashEntry);
    }

    public function testGetNotIn()
    {
        $hashEntry1 = new HashEntry();
        $hashEntry1->file = '/dir/testfile.php';
        $hashEntry1->hash = md5('code');

        $hashEntry2 = new HashEntry();
        $hashEntry2->file = '/dir/testfile2.php';
        $hashEntry2->hash = md5('code2');

        $hashEntry3 = new HashEntry();
        $hashEntry3->file = '/dir/testfile3.php';
        $hashEntry3->hash = md5('code3');

        $hashEntryCollection1 = new HashEntryCollection([
            $hashEntry1,
            $hashEntry2,
            $hashEntry3
        ]);

        $hashEntryCollection2 = new HashEntryCollection([
            $hashEntry1,
            $hashEntry2
        ]);

        $hashEntryCollection = $hashEntryCollection1->getNotIn($hashEntryCollection2);
        $this->assertCount(1, $hashEntryCollection->hashEntries);
        $this->assertEquals($hashEntry3, $hashEntryCollection->hashEntries[0]);

        $hashEntryCollection = $hashEntryCollection2->getNotIn($hashEntryCollection1);
        $this->assertCount(0, $hashEntryCollection->hashEntries);
    }

    public function testGetNotEqualTo()
    {
        $hashEntry1 = new HashEntry();
        $hashEntry1->file = '/dir/testfile.php';
        $hashEntry1->hash = md5('code');

        $hashEntry2 = new HashEntry();
        $hashEntry2->file = '/dir/testfile2.php';
        $hashEntry2->hash = md5('code2');

        $hashEntry3 = new HashEntry();
        $hashEntry3->file = '/dir/testfile2.php';
        $hashEntry3->hash = md5('code2');

        $hashEntry4 = new HashEntry();
        $hashEntry4->file = '/dir/testfile2.php';
        $hashEntry4->hash = md5('code4');

        $hashEntryCollection1 = new HashEntryCollection([
            $hashEntry1,
            $hashEntry2,
            $hashEntry4
        ]);

        $hashEntryCollection2 = new HashEntryCollection([
            $hashEntry2,
            $hashEntry3,
        ]);

        $hashEntryCollection = $hashEntryCollection1->getNotEqualTo($hashEntryCollection2);
        $this->assertCount(1, $hashEntryCollection->hashEntries);
        $this->assertEquals($hashEntry4, $hashEntryCollection->hashEntries[0]);
    }
}
