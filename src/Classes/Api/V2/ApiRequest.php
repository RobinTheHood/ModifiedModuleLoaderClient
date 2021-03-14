<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient\Api\V2;

use Buzz\Browser;
use Buzz\Client\FileGetContents;
use Nyholm\Psr7\Factory\Psr17Factory;
use RobinTheHood\ModifiedModuleLoaderClient\Api\V2\Endpoints\ModulesEndpoint;
use RobinTheHood\ModifiedModuleLoaderClient\Api\V2\Endpoints\AuthenticationEndpoint;

class ApiRequest
{
    private $client;
    private $browser;

    public function __construct()
    {
        $this->client = new FileGetContents(new Psr17Factory());
        $this->browser = new Browser($this->client, new Psr17Factory());
    }

    public function getApiToken()
    {
        $authenticationEndpoint = new AuthenticationEndpoint($this->browser);
        $token = $authenticationEndpoint->getToken([]);
        return $token;
    }

    public function getModules($conditions)
    {
        $modulesEndpoint = new ModulesEndpoint($this->browser);
        $params = $this->convertConditionsToParams($conditions);
        
        $token = $this->getApiToken();
        $modulesEndpoint->setApiToken($token);

        $result = $modulesEndpoint->getAllBy($params);
        
        return $result;
    }

    public function getArchive($archiveName, $version)
    {
    }

    public function getAllVersions()
    {
    }

    private function convertConditionsToParams($conditions)
    {
        $params = [];
        if (isset($conditions['archiveName'])) {
            $params['archiveName'] = $conditions['archiveName'];
        }

        if (isset($conditions['filter']) && $conditions['filter'] === 'latestVersion') {
            $params['latest'] = true;
        }

        if (isset($conditions['version'])) {
            $params['version'] = $conditions['version'];
        }

        return $params;
    }
}
