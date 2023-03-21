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
use RobinTheHood\ModifiedModuleLoaderClient\Api\V1\ApiRequest;
use RobinTheHood\ModifiedModuleLoaderClient\SelfUpdater;

class SelfUpdaterTest extends TestCase
{
    public function getStubedApiRequest()
    {
        /** @var MockObject $stubApiRequest */
        $stubApiRequest = $this->createMock(ApiRequest::class);
        $stubApiRequest->method('getAllVersions')->willReturn([
            'content' => [
                [
                    'version' => '1.1.1-alpha',
                    'fileName' => 'ModifiedModuleLoaderClient_v1.1.1-alpha.tar'
                ],
                [
                    'version' => '1.1.2-alpha',
                    'fileName' => 'ModifiedModuleLoaderClient_v1.1.2-alpha.tar'
                ],
                [
                    'version' => '1.1.2',
                    'fileName' => 'ModifiedModuleLoaderClient_v1.1.2.tar'
                ],
                [
                    'version' => '1.1.3-alpha',
                    'fileName' => 'ModifiedModuleLoaderClient_v1.1.3-alpha.tar'
                ],
            ]
        ]);

        return $stubApiRequest;
    }

    public function getStubedSelfUpdater()
    {
        /** @var MockObject $stubSelfUpdater */
        $stubSelfUpdater = $this->getMockBuilder(SelfUpdater::class)
            ->onlyMethods(['getVersionInfos', 'getInstalledVersion'])
            ->getMock();

        $stubSelfUpdater->method('getVersionInfos')->willReturn([
            [
                'version' => '1.1.1-alpha',
                'fileName' => 'ModifiedModuleLoaderClient_v1.1.1-alpha.tar'
            ],
            [
                'version' => '1.1.2-alpha',
                'fileName' => 'ModifiedModuleLoaderClient_v1.1.2-alpha.tar'
            ],
            [
                'version' => '1.1.2',
                'fileName' => 'ModifiedModuleLoaderClient_v1.1.2.tar'
            ],
            [
                'version' => '1.1.3-alpha',
                'fileName' => 'ModifiedModuleLoaderClient_v1.1.3-alpha.tar'
            ],
            [
                'version' => '1.2.0',
                'fileName' => 'ModifiedModuleLoaderClient_v1.2.0.tar'
            ],
            [
                'version' => '1.2.1',
                'fileName' => 'ModifiedModuleLoaderClient_v1.2.1.tar'
            ],
            [
                'version' => '1.3.0-beta.1',
                'fileName' => 'ModifiedModuleLoaderClient_v1.3.0-beta.1.tar'
            ],
            [
                'version' => '1.3.0',
                'fileName' => 'ModifiedModuleLoaderClient_v1.3.0.tar'
            ],
            [
                'version' => '1.3.1',
                'fileName' => 'ModifiedModuleLoaderClient_v1.3.1.tar'
            ],
            [
                'version' => '2.0.0',
                'fileName' => 'ModifiedModuleLoaderClient_v2.0.0.tar'
            ],
            [
                'version' => '2.1.0',
                'fileName' => 'ModifiedModuleLoaderClient_v2.1.0.tar'
            ]
        ]);

        return $stubSelfUpdater;
    }

    public function testGetInstalledVersion()
    {
        $selfUpdater = new SelfUpdater();
        $version = $selfUpdater->getInstalledVersion();

        $this->assertNotEmpty($version);
        $this->assertIsString($version);
    }

    public function testGetNewestVersionInfo()
    {
        // Arrage
        $stubApiRequest = $this->getStubedApiRequest();
        $selfUpdater = new SelfUpdater($stubApiRequest);

        // Act
        $newestLatestVersionInfo = $selfUpdater->getNewestVersionInfo(true);
        $newestStabelVersionInfo = $selfUpdater->getNewestVersionInfo(false);

        // Assert
        $expectsLatest = [
            'version' => '1.1.3-alpha',
            'fileName' => 'ModifiedModuleLoaderClient_v1.1.3-alpha.tar'
        ];

        $expectsStable = [
            'version' => '1.1.2',
            'fileName' => 'ModifiedModuleLoaderClient_v1.1.2.tar'
        ];

        $this->assertEquals($expectsLatest, $newestLatestVersionInfo);
        $this->assertEquals($expectsStable, $newestStabelVersionInfo);
    }

    public function testGetVersionInfos()
    {
        // Arrage
        $stubApiRequest = $this->getStubedApiRequest();
        $selfUpdater = new SelfUpdater($stubApiRequest);

        // Act
        $versionInfos = $selfUpdater->getVersionInfos(true);

        // Assert
        $expects = [
            [
                'version' => '1.1.1-alpha',
                'fileName' => 'ModifiedModuleLoaderClient_v1.1.1-alpha.tar'
            ],
            [
                'version' => '1.1.2-alpha',
                'fileName' => 'ModifiedModuleLoaderClient_v1.1.2-alpha.tar'
            ],
            [
                'version' => '1.1.2',
                'fileName' => 'ModifiedModuleLoaderClient_v1.1.2.tar'
            ],
            [
                'version' => '1.1.3-alpha',
                'fileName' => 'ModifiedModuleLoaderClient_v1.1.3-alpha.tar'
            ],
        ];

        $this->assertEquals($expects, $versionInfos);
    }

    public function testCanGetNextNewestPathVersionInfo()
    {
        // 1.1.0 to 1.2.0
        $selfUpdater = $this->getStubedSelfUpdater();
        $selfUpdater->method('getInstalledVersion')->willReturn('1.1.0');
        $this->assertEquals(
            [
                'version' => '1.2.0',
                'fileName' => 'ModifiedModuleLoaderClient_v1.2.0.tar'
            ],
            $selfUpdater->getNextNewestVersionInfo()
        );

        // 1.2.0 to 1.3.0
        $selfUpdater = $this->getStubedSelfUpdater();
        $selfUpdater->method('getInstalledVersion')->willReturn('1.2.0');
        $this->assertEquals(
            [
                'version' => '1.3.0',
                'fileName' => 'ModifiedModuleLoaderClient_v1.3.0.tar'
            ],
            $selfUpdater->getNextNewestVersionInfo()
        );

        // 1.3.0 to 1.3.1
        $selfUpdater = $this->getStubedSelfUpdater();
        $selfUpdater->method('getInstalledVersion')->willReturn('1.3.0');
        $this->assertEquals(
            [
                'version' => '1.3.1',
                'fileName' => 'ModifiedModuleLoaderClient_v1.3.1.tar'
            ],
            $selfUpdater->getNextNewestVersionInfo()
        );
    }
}
