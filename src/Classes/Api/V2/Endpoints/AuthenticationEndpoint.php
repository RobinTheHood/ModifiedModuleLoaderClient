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

namespace RobinTheHood\ModifiedModuleLoaderClient\Api\V2\Endpoints;

use Buzz\Browser;
use RobinTheHood\ModifiedModuleLoaderClient\Api\V2\ApiException;
use RobinTheHood\ModifiedModuleLoaderClient\Api\V2\ApiToken;

class AuthenticationEndpoint extends AbstractEndpoint
{
    /** @var string  */
    protected $resourcePath = 'http://app.module-loader.localhost/api/v2/authentication/gettoken';

    public function getApiToken(array $parameters): ApiToken
    {
        $this->convertBoolToString($parameters);

        $header = [];
        $url = $this->resourcePath . '?' . http_build_query($parameters);
        $response = $this->browser->post($url, $header, '{
            "mmlcAccessToken": "abc",
            "domain": "localhost",
            "ip": "192.168.0.1",
            "datetime": "2021-03-15 21:44:00",
            "hash": "qwertzuiop"
          }');

        if ($response->getStatusCode() >= 400) {
            throw new ApiException(
                'AuthenticationEndpoint::getToken() - Error: HTTP Status ' . $response->getStatusCode()
            );
        }

        // var_dump($response->getBody()->getContents());
        // die();

        $array = json_decode($response->getBody()->getContents(), true);

        if (!isset($array['token'])) {
            throw new ApiException(
                'AuthenticationEndpoint::getToken() - Can not request apiToken.'
            );
        }

        return new ApiToken($array['token']);
    }
}
