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
use Buzz\Browser;
use Buzz\Client\FileGetContents;
use Nyholm\Psr7\Factory\Psr17Factory;
use RobinTheHood\ModifiedModuleLoaderClient\Api\V2\Endpoints\ModulesEndpoint;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\ApiV2ModuleConverter;

class ApiV2Test //extends TestCase
{
    /*
    public static function test1()
    {
        $client = new FileGetContents(new Psr17Factory());
        $browser = new Browser($client, new Psr17Factory());

        $modules = new ModulesEndpoint($browser);

        //header('Content-Type: application/json; charset=utf-8');
        $result = $modules->getAllBy([
            'archiveName' => 'robinthehood/modified-std-module',
            'latest' => true,
            //'limit' => 5
        ]);


        $modulesConverter = new ApiV2ModuleConverter();
        $modules = $modulesConverter->convertToModules($result);
        var_dump($modules);

        echo json_encode(json_decode($result), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        die();
    }

    public static function test()
    {
        $apiRequest = new ApiRequest();
        $apiRequest->getApiToken();
        die();
    }
    */
}
