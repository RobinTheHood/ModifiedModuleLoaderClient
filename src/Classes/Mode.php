<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient;


class Mode
{
    public static function isOverview(): bool
    {
        if (Config::getOption('mode') == 'overview') {
            return true;
        }
        return false;
    }

    public static function isStandard(): bool
    {
        $mode = Config::getOption('mode');
        if ($mode === 'standard' || $mode === '' || $mode === null) {
            return true;
        }
        return false;
    }

    public static function dieIsNotStandard(): void
    {
        if (!self::isStandard()) {
            header("HTTP/1.0 404 Not Found");
            die('This action is not allowed in demo mode. Use the back button in you browser.');
        }
    }
}
