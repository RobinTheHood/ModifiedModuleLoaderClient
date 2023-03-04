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

namespace RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher;

use RobinTheHood\ModifiedModuleLoaderClient\FileHasher\ChangedEntry;
use RobinTheHood\ModifiedModuleLoaderClient\FileHasher\ChangedEntryCollection;
use RobinTheHood\ModifiedModuleLoaderClient\FileHasher\Filter;
use RobinTheHood\ModifiedModuleLoaderClient\FileHasher\HashEntryCollection;

class Comparator
{
    /**
     *
     * @param HashEntryCollection $installed
     * @param HashEntryCollection $shop
     * @param HashEntryCollection $mmlc
     *
     * @return ChangedEntryCollection
     */
    public function getChangedEntries(
        HashEntryCollection $installedShop,
        HashEntryCollection $moduleToShop,
        HashEntryCollection $module
    ): ChangedEntryCollection {
        $filer = new Filter();
        $changeEntryCollections = [];
        $changeEntryCollections[] = $filer->getANotInB($module, $installedShop, ChangedEntry::TYPE_NEW);
        $changeEntryCollections[] = $filer->getANotInB($installedShop, $moduleToShop, ChangedEntry::TYPE_DELETED);
        $changeEntryCollections[] = $filer->getANotEqualToB($installedShop, $moduleToShop, ChangedEntry::TYPE_CHANGED);
        $changeEntryCollections[] = $filer->getANotEqualToB($module, $installedShop, ChangedEntry::TYPE_CHANGED);
        return ChangedEntryCollection::merge($changeEntryCollections)->unique();
    }
}
