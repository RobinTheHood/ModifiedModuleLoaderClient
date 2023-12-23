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

namespace RobinTheHood\ModifiedModuleLoaderClient;

use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;

class SemverComparatorFactory
{
    /**
     * @return Comparator
     */
    public static function createComparator()
    {
        return Comparator::create(Config::getDependenyMode());
    }
}
