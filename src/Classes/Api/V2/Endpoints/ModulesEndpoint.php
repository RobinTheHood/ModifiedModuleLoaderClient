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
use RobinTheHood\ModifiedModuleLoaderClient\Notification;

class ModulesEndpoint extends AbstractEndpoint
{
    /** @var string  */
    protected $resourcePath = 'http://app.module-loader.localhost/api/v2/modules';


    public function getAllBy(array $parameters): string
    {
        $this->convertBoolToString($parameters);

        $header = [];
        if ($this->apiToken) {
            $header['Authorization'] = $this->apiToken->createBearer();
        }

        $url = $this->resourcePath . '?' . http_build_query($parameters);
        $response = $this->browser->get($url, $header);

        if ($response->getStatusCode() >= 400) {
            throw new ApiException(
                'ModulesEndpoint::getAllBy - Error: HTTP Status ' . $response->getStatusCode()
            );
        }

        return $response->getBody()->getContents();
    }
}
