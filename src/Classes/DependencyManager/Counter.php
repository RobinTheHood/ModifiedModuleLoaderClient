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

class Counter
{
    private $counterMaxValues = [];
    private $counterValues = [];

    public function __construct(array $maxValues)
    {
        $this->counterMaxValues = $maxValues;
        foreach ($this->counterMaxValues as $counterMaxValue) {
            $this->counterValues[] = 0;
        }
    }

    public function current(): array
    {
        return $this->counterValues;
    }

    public function next(): array
    {
        $this->incrementCounterAtIndex(0);
        return $this->counterValues;
    }

    public function isStart(): bool
    {
        foreach ($this->counterValues as $value) {
            if ($value !== 0) {
                return false;
            }
        }
        return true;
    }

    private function incrementCounterAtIndex(int $counterIndex)
    {
        if ($counterIndex > count($this->counterMaxValues) - 1) {
            return true;
        }

        if ($this->incrementCounterAtIndex($counterIndex + 1)) {
            $this->counterValues[$counterIndex]++;
        }

        if ($this->counterValues[$counterIndex] > $this->counterMaxValues[$counterIndex]) {
            $this->counterValues[$counterIndex] = 0;
            return true;
        }

        return false;
    }
}
