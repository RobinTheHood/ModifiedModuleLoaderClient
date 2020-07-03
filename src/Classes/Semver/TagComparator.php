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

namespace RobinTheHood\ModifiedModuleLoaderClient\Semver;

use RobinTheHood\ModifiedModuleLoaderClient\Helpers\ArrayHelper;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;

class TagComparator
{
    /**
      * @var array<string, int>
      */
    protected $map = [
        'alpha' => 10,
        'alpha.0' => 10,
        'alpha.1' => 11,
        'alpha.2' => 12,
        'alpha.3' => 13,
        'alpha.4' => 14,
        'alpha.5' => 15,
        'alpha.6' => 16,
        'alpha.7' => 17,
        'alpha.8' => 18,
        'alpha.9' => 19,
        'beta' => 20,
        'beta.0' => 20,
        'beta.1' => 21,
        'beta.2' => 22,
        'beta.3' => 23,
        'beta.4' => 24,
        'beta.5' => 25,
        'beta.6' => 26,
        'beta.7' => 27,
        'beta.8' => 28,
        'beta.9' => 29,
        'rc' => 30,
        'rc.0' => 30,
        'rc.1' => 31,
        'rc.2' => 32,
        'rc.3' => 33,
        'rc.4' => 34,
        'rc.5' => 35,
        'rc.6' => 36,
        'rc.7' => 37,
        'rc.8' => 38,
        'rc.9' => 39,
        '' => 100
    ];

    public function equalTo(string $string1, string $string2): bool
    {
        if ($string1 === $string2) {
            return true;
        }
        return false;
    }

    public function greaterThan(string $string1, string $string2): bool
    {
        if ($string1 === '' && $string2 !== '') {
            return true;
        }

        if ($string1 !== '' && $string2 === '') {
            return false;
        }

        $value1 = ArrayHelper::getIfSet($this->map, $string1, 0);
        $value2 = ArrayHelper::getIfSet($this->map, $string2, 0);
        return $value1 > $value2;
    }

    public function greaterThanOrEqualTo(string $string1, string $string2): bool
    {
        if ($this->greaterThan($string1, $string2)) {
            return true;
        }

        if ($this->equalTo($string1, $string2)) {
            return true;
        }

        return false;
    }

    public function lessThan(string $string1, string $string2): bool
    {
        if (!$this->greaterThanOrEqualTo($string1, $string2)) {
            return true;
        }

        return false;
    }

    public function lessThanOrEqualTo(string $string1, string $string2): bool
    {
        if (!$this->greaterThan($string1, $string2)) {
            return true;
        }

        return false;
    }
}