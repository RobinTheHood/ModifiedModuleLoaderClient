<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient\Helpers;

class ArrayHelper
{
    public static function getIfSet(array $array, $index, $default = '')
    {
        return empty($array[$index]) ? $default : $array[$index];
    }
}
