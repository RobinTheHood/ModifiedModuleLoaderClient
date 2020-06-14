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

namespace RobinTheHood\ModifiedModuleLoaderClient\Helpers;

class IntegerHelper
{
    public static function isInteger(string $value): bool
    {
        $intValue = (int) $value;
        $stringValue = (string) $intValue;

        if ($stringValue === $value) {
            return true;
        }

        return false;
    }
}