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

class CombinationSatisfyerResult
{
    /** @var Combination */
    public $testCombination = null;
    /** @var Combination */
    public $foundCombination = null;

    public function __construct(?Combination $testCombination, ?Combination $foundCombination)
    {
        $this->testCombination = $testCombination;
        $this->foundCombination = $foundCombination;
    }
}