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

abstract class AbstractEndpoint
{
    /** @var Browser  */
    protected $browser;

    /** @var string */
    protected $resourcePath;

    public function __construct(\Buzz\Browser $browser)
    {
        $this->browser = $browser;
    }

    protected function convertBoolToString(array &$parameters)
    {
        foreach ($parameters as &$parameter) {
            if ($parameter === true) {
                $parameter = 'true';
            }
        }
    }
}
