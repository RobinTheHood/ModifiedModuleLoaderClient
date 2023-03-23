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
use RobinTheHood\ModifiedModuleLoaderClient\MmlcVersionInfo;
use RobinTheHood\ModifiedModuleLoaderClient\MmlcVersionInfoLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Filter;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Sorter;

class MmlcVersionInfoLoaderTest extends TestCase
{
    public function testCanGetAll()
    {
        $mmlcVersionInfoLoader = $this->getMmlcVersionInfoLoader();

        $mmlcVersionInfos = $mmlcVersionInfoLoader->getAll();

        $this->assertContainsOnlyInstancesOf(MmlcVersionInfo::class, $mmlcVersionInfos);
        $this->assertCount(13, $mmlcVersionInfos);
        $this->assertEquals('1.1.1-alpha', $mmlcVersionInfos[0]->version);
        $this->assertEquals('ModifiedModuleLoaderClient_v1.1.1-alpha.tar', $mmlcVersionInfos[0]->fileName);
    }

    public function testCanGetNewestStable()
    {
        $mmlcVersionInfoLoader = $this->getMmlcVersionInfoLoader();

        $mmlcVersionInfo = $mmlcVersionInfoLoader->getNewest();
        $this->assertEquals('2.1.0', $mmlcVersionInfo->version);
    }

    public function testCanGetNewestUnstable()
    {
        $mmlcVersionInfoLoader = $this->getMmlcVersionInfoLoader();

        $mmlcVersionInfo = $mmlcVersionInfoLoader->getNewest(true);
        $this->assertEquals('2.2.0-beta', $mmlcVersionInfo->version);
    }

    public function testCanGetNextNewestStable()
    {
        $mmlcVersionInfoLoader = $this->getMmlcVersionInfoLoader();

        $mmlcVersionInfo = $mmlcVersionInfoLoader->getNextNewest('1.2.1');
        $this->assertEquals('1.3.0', $mmlcVersionInfo->version);
    }

    public function testCanGetNextNewestUnstable()
    {
        $mmlcVersionInfoLoader = $this->getMmlcVersionInfoLoader();

        $mmlcVersionInfo = $mmlcVersionInfoLoader->getNextNewest('1.2.1', true);
        $this->assertEquals('1.3.0', $mmlcVersionInfo->version);

        $mmlcVersionInfo = $mmlcVersionInfoLoader->getNextNewest('2.1.0', true);
        $this->assertEquals('2.2.0-beta', $mmlcVersionInfo->version);
    }

    private function getMmlcVersionInfoLoader(): MmlcVersionInfoLoader
    {
        $parser = new Parser();
        $comparator = new Comparator($parser);
        $sorter = new Sorter($comparator);
        $filter = new Filter($parser, $comparator, $sorter);

        $mmlcVersionInfoLoader = new MmlcVersionInfoLoader(
            $this->getMockedApiRequest(),
            $parser,
            $filter
        );

        return $mmlcVersionInfoLoader;
    }

    private function getMockedApiRequest(): ApiRequest
    {
        /** @var MockObject $mockedApiRequest */
        $mockedApiRequest = $this->createMock(ApiRequest::class);
        $mockedApiRequest->method('getAllVersions')->willReturn([
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
                ],
                [
                    'version' => '2.1.1-beta',
                    'fileName' => 'ModifiedModuleLoaderClient_v2.1.1-beta.tar'
                ],
                [
                    'version' => '2.2.0-beta',
                    'fileName' => 'ModifiedModuleLoaderClient_v2.2.0-beta.tar'
                ]
            ]
        ]);

        /** @var ApiRequest $mockedApiRequest */
        return $mockedApiRequest;
    }
}
