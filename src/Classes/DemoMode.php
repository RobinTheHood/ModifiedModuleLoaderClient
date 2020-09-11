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


class DemoMode
{
    const DEMO_MODE = true;

    public static function isDemo(): bool
    {
        if (Config::getOption('demoMode') == 'true') {
            return true;
        }
        return false;
    }

    public static function isNotDemo(): bool
    {
        return !self::isDemo();
    }

    public static function dieIsDemo(): void
    {
        if (self::isDemo()) {
            header("HTTP/1.0 404 Not Found");
            die('This action is not allowed in demo mode. Use the back button in you browser.');
        }
    }
}
