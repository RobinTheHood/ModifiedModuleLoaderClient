<?php

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
use RobinTheHood\ModifiedModuleLoaderClient\Notification;

class AuthenticationEndpoint extends AbstractEndpoint
{
    /** @var string  */
    protected $resourcePath = 'http://app.module-loader.localhost/api/v2/authentication/gettoken';

    public function getToken(array $parameters)
    {
        $this->convertBoolToString($parameters);

        $header = [];
        $url = $this->resourcePath . '?' . http_build_query($parameters);
        $response = $this->browser->post($url, $header);

        if ($response->getStatusCode() >= 400) {
            //throw new ApiException();

            // TODO: DO NOT USE Notification, throw ApiException
            Notification::pushFlashMessage([
                'text' => 'Error: Bad response. {MESSAGE}',
                'type' => 'error'
            ]);
        }

        $array = json_decode($response->getBody()->getContents(), true);
        $token = $array['token'] ?? '';

        return $token;
    }
}
