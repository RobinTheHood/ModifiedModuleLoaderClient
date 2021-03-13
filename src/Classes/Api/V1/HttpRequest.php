<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient\Api\V1;

class HttpRequest
{
    public function isServerAvailable($url)
    {
        $headers = @get_headers($url);
        if ($headers) {
            return true;
        }
        return false;
    }

    public function sendPostRequest($url, $data)
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
        return @file_get_contents($url, false, $context);
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
        return @file_get_contents($url, false, $context);
    }

    public static function createQuery($queryValues)
    {
        $query = '';
        foreach ($queryValues as $name => $value) {
            $query .= $name . '=' . urlencode($value) . '&';
        }
        return $query;
    }
}
