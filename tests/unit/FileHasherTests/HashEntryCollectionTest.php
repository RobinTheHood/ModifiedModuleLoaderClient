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
use RobinTheHood\ModifiedModuleLoaderClient\FileHasher\HashEntry;
use RobinTheHood\ModifiedModuleLoaderClient\FileHasher\HashEntryCollection;

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

    public function testToArray()
    {
        $hashEntry1 = new HashEntry();
        $hashEntry1->file = '/dir/testfile.php';
        $hashEntry1->hash = md5('code');

        $hashEntryCollection = new HashEntryCollection([
            $hashEntry1
        ]);

        $array = $hashEntryCollection->toArray();

        $this->assertEquals(['/dir/testfile.php' => md5('code')], $array);
    }
}
