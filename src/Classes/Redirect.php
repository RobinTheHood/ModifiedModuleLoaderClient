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

class Redirect
{
    public static function redirect(string $url, string $domain = ''): void
    {
        self::status302($url, $domain);
        exit();
    }

    public static function status302(string $url, string $domain = ''): void
    {
        $host  = $_SERVER['HTTP_HOST'];
        if ($domain) {
            $host = $domain;
        }

        $protocoll = self::getProtocoll();
        $path   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

        header('Location: ' . $protocoll . '://' . $host . $path . $url);
        exit();
    }

    public static function status404(string $url): void
    {
        $host  = $_SERVER['HTTP_HOST'];

        $protocoll = self::getProtocoll();

        $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        header("HTTP/1.0 404 Not Found");
        header("Location: $protocoll://$host$uri$url");
        exit();
    }

    public static function getProtocoll(): string
    {
        if (empty($_SERVER['HTTPS'])) {
            return 'http';
        } else {
            return 'https';
        }
    }
}
