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

namespace RobinTheHood\ModifiedModuleLoaderClient\Tests\Unit\ModuleManagerTests;

use Exception;
use PHPUnit\Framework\TestCase;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleManager\AutoloadEntry;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleManager\AutoloadEntryCollection;

class AutoloadEntryCollectionTest extends TestCase
{
    public function testAddAutoloadEntry(): void
    {
        $collection = new AutoloadEntryCollection();
        $autoloadEntry = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace', 'src-mmlc', '/path/to/f1');

        $collection->add($autoloadEntry);

        $this->assertEquals([$autoloadEntry], $collection->getAutoloadEntries());
    }

    public function testAddDuplicateNamespace(): void
    {
        $this->expectException(Exception::class);

        $collection = new AutoloadEntryCollection();
        $autoloadEntry1 = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace', 'src-mmlc', '/path/to/f1');
        $autoloadEntry2 = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace', 'src-mmlc', '/path/to/f2');

        $collection->add($autoloadEntry1);
        $collection->add($autoloadEntry2);
    }

    public function testAddDuplicatePath(): void
    {
        $this->expectException(Exception::class);

        $collection = new AutoloadEntryCollection();
        $autoloadEntry1 = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace1', 'src-mmlc', '/path/to/f1');
        $autoloadEntry2 = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace2', 'src-mmlc', '/path/to/f1');

        $collection->add($autoloadEntry1);
        $collection->add($autoloadEntry2);
    }

    public function testUniqueCollection(): void
    {
        $collection = new AutoloadEntryCollection();
        $autoloadEntry1 = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace1', 'src-mmlc', '/path/to/f1');
        $autoloadEntry2 = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace2', 'src-mmlc', '/path/to/f2');
        $autoloadEntry3 = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace2', 'src-mmlc', '/path/to/f2');

        $collection->add($autoloadEntry1);
        $collection->add($autoloadEntry2);
        $collection->add($autoloadEntry3);

        $uniqueCollection = $collection->unique();

        $this->assertEquals([$autoloadEntry1, $autoloadEntry2], $uniqueCollection->getAutoloadEntries());
    }

    public function testGetEntryByNamespace(): void
    {
        $collection = new AutoloadEntryCollection();
        $autoloadEntry1 = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace1', 'src-mmlc', '/path/to/f1');
        $autoloadEntry2 = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace2', 'src-mmlc', '/path/to/f2');

        $collection->add($autoloadEntry1);
        $collection->add($autoloadEntry2);

        $resultEntry = $collection->getEntryByNamespace('Namespace1');

        $this->assertEquals($autoloadEntry1, $resultEntry);
    }

    public function testGetEntryByNamespaceNotFound(): void
    {
        $collection = new AutoloadEntryCollection();
        $autoloadEntry = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace1', 'src-mmlc', '/path/to/f1');

        $collection->add($autoloadEntry);

        $resultEntry = $collection->getEntryByNamespace('NonExistentNamespace');

        $this->assertNull($resultEntry);
    }

    public function testGetEntryByRealPath(): void
    {
        $collection = new AutoloadEntryCollection();
        $autoloadEntry1 = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace1', 'src-mmlc', '/path/to/f1');
        $autoloadEntry2 = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace2', 'src-mmlc', '/path/to/f2');

        $collection->add($autoloadEntry1);
        $collection->add($autoloadEntry2);

        $resultEntry = $collection->getEntryByRealPath('/path/to/f1');

        $this->assertEquals($autoloadEntry1, $resultEntry);
    }

    public function testGetEntryByRealPathNotFound(): void
    {
        $collection = new AutoloadEntryCollection();
        $autoloadEntry = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace1', 'src-mmlc', '/path/to/f1');

        $collection->add($autoloadEntry);

        $resultEntry = $collection->getEntryByRealPath('/nonexistent/path');

        $this->assertNull($resultEntry);
    }

    public function testMergeWith(): void
    {
        $collection1 = new AutoloadEntryCollection();
        $autoloadEntry1 = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace1', 'src-mmlc', '/path/to/f1');
        $autoloadEntry2 = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace2', 'src-mmlc', '/path/to/f2');
        $collection1->add($autoloadEntry1);
        $collection1->add($autoloadEntry2);

        $collection2 = new AutoloadEntryCollection();
        $autoloadEntry3 = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace3', 'src-mmlc', '/path/to/f3');
        $autoloadEntry4 = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace4', 'src-mmlc', '/path/to/f4');
        $collection2->add($autoloadEntry3);
        $collection2->add($autoloadEntry4);

        $mergedCollection = $collection1->mergeWith($collection2);

        $expectedEntries = [$autoloadEntry1, $autoloadEntry2, $autoloadEntry3, $autoloadEntry4];
        $this->assertEquals($expectedEntries, $mergedCollection->getAutoloadEntries());
    }

    public function testMergeWithEmptyCollection(): void
    {
        $collection1 = new AutoloadEntryCollection();
        $autoloadEntry1 = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace1', 'src-mmlc', '/path/to/f1');
        $autoloadEntry2 = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace2', 'src-mmlc', '/path/to/f2');
        $collection1->add($autoloadEntry1);
        $collection1->add($autoloadEntry2);

        $collection2 = new AutoloadEntryCollection();

        $mergedCollection = $collection1->mergeWith($collection2);

        $this->assertEquals($collection1->getAutoloadEntries(), $mergedCollection->getAutoloadEntries());
    }

    public function testIterator(): void
    {
        $collection = new AutoloadEntryCollection();
        $autoloadEntry1 = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace1', 'src-mmlc', '/path/to/f1');
        $autoloadEntry2 = new AutoloadEntry(AutoloadEntry::TYPE_PSR_4, null, 'Namespace2', 'src-mmlc', '/path/to/f2');
        $collection->add($autoloadEntry1);
        $collection->add($autoloadEntry2);

        $resultEntries = [];

        foreach ($collection as $autoloadEntry) {
            $resultEntries[] = $autoloadEntry;
        }

        $this->assertEquals([$autoloadEntry1, $autoloadEntry2], $resultEntries);
    }
}
