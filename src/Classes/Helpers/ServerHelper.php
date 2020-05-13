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

class ServerHelper
{
    public static function getUri()
    {
        $http = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
        $url = $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];
        $parts = pathinfo($url);
        return $http . $parts['dirname'];
    }

    public static function urlExists($url)
    {
        $headers = @get_headers($url);

        if(strpos($headers[0], '200')) {
            return true;
        }

        return false;
    }
}
