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

class ApiRequest extends ApiBaseRequest
{
    public function __construct()
    {
        global $configuration;
        $this->setUrl($configuration['remoteAddress']);
        $this->setAccessToken($configuration['accessToken']);
    }

    public function getModules($conditions)
    {
        $conditionStr = $this->buildConditionString($conditions);

        return $this->sendRequest('
            {
                allModules' . $conditionStr . ' {
                }
            }
        ');
    }

    public function getArchive($archiveName, $version)
    {
        return $this->sendRequest('
            {
                Archive(archiveName: "' . $archiveName . '", version: "' . $version . '") {
                }
            }
        ');
    }

    public function getAllVersions()
    {
        return $this->sendRequest('
            {
                allVersions {
                }
            }
        ');
    }

    public function buildConditionString($conditions): string
    {
        $conditionStr = '';
        foreach ($conditions as $name => $value) {
            if ($conditionStr) {
                $conditionStr .= ', ';
            }
            $conditionStr .= $name . ': "' . $value . '"';
        }

        if ($conditionStr) {
            $conditionStr = '(' . $conditionStr . ')';
        }

        return $conditionStr;
    }
}
