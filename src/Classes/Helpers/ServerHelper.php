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

class ServerHelper
{
    public static function getUri(): string
    {
        $http = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
        $serverName = $_SERVER['SERVER_NAME'] ?? 'unknown-server-name.de';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/unknown-scriptname.php';
        $url = $serverName . $scriptName;
        $parts = pathinfo($url);
        return $http . $parts['dirname'];
    }

    public static function urlExists(string $url): bool
    {
        $headers = @get_headers($url);

        if (isset($headers[0]) && strpos($headers[0], '200')) {
            return true;
        }

        return false;
    }
}
