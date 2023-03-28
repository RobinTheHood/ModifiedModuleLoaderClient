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
    public const RESULT_COMBINATION_NOT_FOUND = 0;
    public const RESULT_COMBINATION_FOUND = 1;

    /** @var int */
    public $result = -1;

    /** @var ?Combination */
    public $testCombination = null;

    /** @var ?Combination */
    public $foundCombination = null;

    /** @var ?FailLog */
    public $failLog = null;

    public function __construct(
        int $result,
        ?Combination $testCombination,
        ?Combination $foundCombination,
        ?FailLog $failLog
    ) {
        $this->result = $result;
        $this->testCombination = $testCombination;
        $this->foundCombination = $foundCombination;
        $this->failLog = $failLog;
    }
}
