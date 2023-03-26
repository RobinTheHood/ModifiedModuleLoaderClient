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

namespace RobinTheHood\ModifiedModuleLoaderClient\DependencyManager;

use Exception;

class FlatEntry
{
    /** @var string */
    public $archiveName;

    /** @var array version strings */
    public $versions = [];

    public function combine(FlatEntry $flatEntry): void
    {
        if ($this->archiveName !== $flatEntry->archiveName) {
            throw new Exception("Cant combine FlatEntry {$this->archiveName} and {$flatEntry->archiveName}");
        }

        $this->versions = array_merge($this->versions, $flatEntry->versions);
        $this->versions = array_unique($this->versions);
        $this->versions = array_values($this->versions);
    }
}
