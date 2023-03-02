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
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\ChangedEntry;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\Comparator;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\HashEntry;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\HashEntryCollection;

class ComparatorTest extends TestCase
{
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

        $new = $changedEntryCollection->getByType(ChangedEntry::TYPE_NEW);
        $deleted = $changedEntryCollection->getByType(ChangedEntry::TYPE_DELETED);
        $changed = $changedEntryCollection->getByType(ChangedEntry::TYPE_CHANGED);

        $this->assertEquals('/dir/testfile4.php', $changedEntryCollection->changedEntries[0]->file);
        $this->assertEquals(ChangedEntry::TYPE_NEW, $changedEntryCollection->changedEntries[0]->type);

        $this->assertEquals('/dir/testfile1.php', $changedEntryCollection->changedEntries[1]->file);
        $this->assertEquals(ChangedEntry::TYPE_DELETED, $changedEntryCollection->changedEntries[1]->type);

        $this->assertEquals('/dir/testfile2.php', $changedEntryCollection->changedEntries[2]->file);
        $this->assertEquals(ChangedEntry::TYPE_CHANGED, $changedEntryCollection->changedEntries[2]->type);
    }
}
