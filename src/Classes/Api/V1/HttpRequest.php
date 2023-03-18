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

use RobinTheHood\ModifiedModuleLoaderClient\Logger;

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

        Logger::staticLog('debug', "Send POST request to $url. Options: " . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        $result = @file_get_contents($url, false, $context);

        Logger::staticLog('debug', "Response: $result");

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

        Logger::staticLog('debug', "Send GET request to $url. Options: " . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        $result = @file_get_contents($url, false, $context);

        Logger::staticLog('debug', "Response: $result");

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
