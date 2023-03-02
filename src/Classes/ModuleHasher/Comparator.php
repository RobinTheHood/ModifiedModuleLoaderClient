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
        HashEntryCollection $installed,
        HashEntryCollection $shop,
        HashEntryCollection $mmlc
    ): ChangedEntryCollection {
        $new = $mmlc->getNotIn($installed);
        $deleted = $installed->getNotIn($shop);
        $changed1 = $installed->getNotEqualTo($shop);
        $changed2 = $mmlc->getNotEqualTo($installed);

        $changeEntryCollections = [];

        $changeEntryCollections[]
            = ChangedEntryCollection::createFromHashEntryCollection($new, ChangedEntry::TYPE_NEW);
        $changeEntryCollections[]
            = ChangedEntryCollection::createFromHashEntryCollection($deleted, ChangedEntry::TYPE_DELETED);
        $changeEntryCollections[]
            = ChangedEntryCollection::createFromHashEntryCollection($changed1, ChangedEntry::TYPE_CHANGED);
        $changeEntryCollections[]
            = ChangedEntryCollection::createFromHashEntryCollection($changed2, ChangedEntry::TYPE_CHANGED);

        return ChangedEntryCollection::merge($changeEntryCollections);
    }
}
