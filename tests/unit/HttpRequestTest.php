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
use RobinTheHood\ModifiedModuleLoaderClient\Api\V1\HttpRequest;

class HttpRequestTest extends TestCase
{
    public function testSendPostRequestSuccess()
    {
        $httpClient = new HttpRequest();
        $response = $httpClient->sendPostRequest('https://postman-echo.com/post', ['foo' => 'bar']);

        $this->assertNotNull($response);
        $this->assertIsString($response);
        $this->assertStringContainsString('application/x-www-form-urlencoded', $response);
        $this->assertStringContainsString('"foo": "bar"', $response);
    }

    public function testSendPostRequestFailure()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches(
            '/Error sending the POST request:/'
        );

        $httpClient = new HttpRequest();
        $response = $httpClient->sendPostRequest('https://this-url-does-not-exist.com', []);
        $this->assertIsString($response);
    }
}
