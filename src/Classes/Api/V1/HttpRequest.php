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

namespace RobinTheHood\ModifiedModuleLoaderClient\Api\V1;

use RobinTheHood\ModifiedModuleLoaderClient\Logger\LogLevel;
use RobinTheHood\ModifiedModuleLoaderClient\Logger\StaticLogger;

class HttpRequest
{
    public function isServerAvailable(string $url): bool
    {
        $headers = @get_headers($url);
        if ($headers) {
            return true;
        }
        return false;
    }

    public function sendPostRequest(string $url, $data)
    {
        // http verwenden, auch wenn die Url mit https://... beginnt
        $options = [
            'http' => [
                'user_agent' => 'Modified Module Loader Client',
                'method' => 'POST',
                'header' => implode("\r\n", [
                    'Content-type: application/x-www-form-urlencoded;'
                ]),
                'content' => http_build_query($data)
            ]
        ];
        $context  = stream_context_create($options);

        StaticLogger::log(
            LogLevel::DEBUG,
            "Send POST request to $url\n[OPTIONS]\n" . print_r($options, true) . "[DATA]\n" . print_r($data, true)
        );

        $timeBeforeRequest = microtime(true);
        $result = @file_get_contents($url, false, $context);
        $time = microtime(true) - $timeBeforeRequest;

        StaticLogger::log(LogLevel::DEBUG, "Response from $url ($time sec)\n" . print_r($result, true));

        return $result;
    }

    public function sendGetRequest($url)
    {
        // http verwenden, auch wenn die Url mit https://... beginnt
        $options = [
            'http' => [
                'user_agent' => 'Modified Module Loader Client',
                'method' => "GET",
                'header' => implode("\r\n", [
                    'Content-type: text/plain;'
                ])
            ]
        ];

        $context = stream_context_create($options);

        StaticLogger::log(
            LogLevel::DEBUG,
            "Send GET request to $url\n[OPTIONS]\n" . print_r($options, true)
        );

        $result = @file_get_contents($url, false, $context);

        StaticLogger::log(LogLevel::DEBUG, "Response from $url\n" . print_r($result, true));

        return $result;
    }

    public static function createQuery(array $queryValues): string
    {
        $query = '';
        foreach ($queryValues as $name => $value) {
            $query .= $name . '=' . urlencode($value) . '&';
        }
        return $query;
    }
}
