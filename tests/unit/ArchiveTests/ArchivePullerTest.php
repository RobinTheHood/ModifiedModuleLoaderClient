<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace RobinTheHood\ModifiedModuleLoaderClient\Tests\Unit\ArchiveTests;

use PHPUnit\Framework\TestCase;
use RobinTheHood\ModifiedModuleLoaderClient\Api\V1\ApiRequest;
use RobinTheHood\ModifiedModuleLoaderClient\Api\V1\HttpRequest;
use RobinTheHood\ModifiedModuleLoaderClient\Archive\Archive;
use RobinTheHood\ModifiedModuleLoaderClient\Archive\ArchivePuller;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;
use RuntimeException;

class ArchivePullerTest extends TestCase
{
    /** @var string */
    protected $testDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Testordner erstellen
        $this->testDir = __DIR__ . '/test_files/';
        @mkdir($this->testDir, 0777, true);

        // Testorder und Dateo im Ordner erstellen

        @mkdir($this->testDir . 'Archives', 0777, true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Testdateien löschen
        @unlink($this->testDir . 'Archives/robinthehood_modified-std-module_0.9.0.tar');
        @rmdir($this->testDir . 'Archives');

        @rmdir($this->testDir);
    }

    public function testPullValidArchive(): void
    {
        $archivesRootPath = $this->testDir . 'Archives';

        $archivePuller = new ArchivePuller(
            new HttpRequest(),
            Parser::create(),
            $archivesRootPath
        );

        $apiRequest = new ApiRequest();
        $result = $apiRequest->getArchive('robinthehood/modified-std-module', '0.9.0');
        var_dump($result);
        $content = $result['content'] ?? [];
        $archiveUrl = $content['archiveUrl'] ?? '';
        $archive = $archivePuller->pull('robinthehood/modified-std-module', '0.9.0', $archiveUrl);

        $this->assertInstanceOf(Archive::class, $archive);
        $this->assertSame('robinthehood/modified-std-module', $archive->getArchiveName()->__toString());
        $this->assertSame('0.9.0', $archive->getVersion()->__toString());
        $this->assertSame($archivesRootPath . '/robinthehood_modified-std-module_0.9.0.tar', $archive->getFilePath());
    }

    public function testPullArchiveFromInvalidUrl(): void
    {
        $archivesRootPath = $this->testDir . 'Archives';

        $archivePuller = new ArchivePuller(
            new HttpRequest(),
            Parser::create(),
            $archivesRootPath
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Fehler beim Senden des GET-Requests: Could not resolve host: this-is-a-not-working-url.local'
        );

        $archivePuller->pull('robinthehood/modified-std-module', '0.9.0', 'this-is-a-not-working-url.local');
    }

    public function testPullInvalidArchive(): void
    {
        $archivesRootPath = $this->testDir . 'Archives';

        $archivePuller = new ArchivePuller(
            new HttpRequest(),
            Parser::create(),
            $archivesRootPath
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to pull Archive: robinthehood/modified-std-module:0.9.0');

        $archivePuller->pull('robinthehood/modified-std-module', '0.9.0', 'https://postman-echo.com/get');
    }
}
