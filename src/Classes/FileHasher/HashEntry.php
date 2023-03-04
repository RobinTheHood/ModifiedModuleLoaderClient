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

namespace RobinTheHood\ModifiedModuleLoaderClient\FileHasher;

class HashEntry
{
    /** @var string $file */
    public $file;

    /** @var string $scope */
    public $scope;

    /** @var string $hash */
    public $hash;

    // TODO add test for clone
    public function clone(): HashEntry
    {
        $hashEntry = new HashEntry();
        $hashEntry->file = $this->file;
        $hashEntry->scope = $this->scope;
        $hashEntry->hash = $this->hash;
        return $hashEntry;
    }
}
