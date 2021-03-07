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

namespace RobinTheHood\ModifiedModuleLoaderClient\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RobinTheHood\ModifiedModuleLoaderClient\IndexController;
use RobinTheHood\ModifiedModuleLoaderClient\Config;
use Nyholm\Psr7\Factory\Psr17Factory;

class ConfigControllerTest extends TestCase
{
    public function testCanSetUserName()
    {
        $session = ['accessRight' => true];
        $originalUserName = Config::getUserName();
        $newUserName = random_bytes(5);
        $psr17Factory = new Psr17Factory();

        $serverRequest = $psr17Factory->createServerRequest('POST', '')
            ->withQueryParams(['action' => 'settings'])
            ->withParsedBody(['username' => $newUserName]);
    
        $controller = new IndexController($serverRequest, $session);
        $result = $controller->invoke();
        Config::reloadConfiguration();
        $this->assertEquals($newUserName, Config::getUserName());

        $serverRequest = $serverRequest->withParsedBody(['username' => $originalUserName]);
        $controller = new IndexController($serverRequest, $session);
        $result = $controller->invoke();

        //Config::reloadConfiguration();
        $this->assertEquals($originalUserName, Config::getUserName());
    }
}
