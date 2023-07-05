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
use RobinTheHood\ModifiedModuleLoaderClient\Archive\Archive;
use RobinTheHood\ModifiedModuleLoaderClient\Archive\ArchiveHandler;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;

class ArchiveHandlerTest extends TestCase
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
        @mkdir($this->testDir . 'ModulesExtracted', 0777, true);
        @mkdir($this->testDir . 'Modules/robinthehood/modified-std-module/1.2.3', 0777, true);
        file_put_contents(
            $this->testDir . 'Modules/robinthehood/modified-std-module/1.2.3/moduleinfo.json',
            '{
                "archiveName": "robinthehood/modified-std-module",
                "version": "1.2.3"
            }'
        );

        @mkdir($this->testDir . 'Archives', 0777, true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Testdateien löschen
        unlink($this->testDir . 'Modules/robinthehood/modified-std-module/1.2.3/moduleinfo.json');
        rmdir($this->testDir . 'Modules/robinthehood/modified-std-module/1.2.3');
        rmdir($this->testDir . 'Modules/robinthehood/modified-std-module');
        rmdir($this->testDir . 'Modules/robinthehood');
        rmdir($this->testDir . 'Modules');

        unlink($this->testDir . 'ModulesExtracted/robinthehood/modified-std-module/1.2.3/moduleinfo.json');
        rmdir($this->testDir . 'ModulesExtracted/robinthehood/modified-std-module/1.2.3');
        rmdir($this->testDir . 'ModulesExtracted/robinthehood/modified-std-module');
        rmdir($this->testDir . 'ModulesExtracted/robinthehood');
        rmdir($this->testDir . 'ModulesExtracted');

        unlink($this->testDir . 'Archives/robinthehood_modified-std-module_1.2.3.tar');
        rmdir($this->testDir . 'Archives');

        rmdir($this->testDir);
    }

    public function testPackAndExtract(): void
    {
        $mode = 0; // Set the appropriate mode
        $archiveName = 'robinthehood/modified-std-module';
        $version = '1.2.3';
        $archivesRootPath = $this->testDir . 'Archives';
        $modulesRootPath = $this->testDir . 'Modules';
        $modulesExtractedRootPath = $this->testDir . 'ModulesExtracted';

        // Create archive
        $archive = Archive::create($archiveName, $version, $archivesRootPath);

        // Create ArchiveHandler
        $localModuleLoader = LocalModuleLoader::create(0);
        $localModuleLoader->setModulesRootPath($modulesRootPath);
        $archiveHandler = new ArchiveHandler($localModuleLoader, $modulesRootPath);

        // Pack archive
        $archiveHandler->pack($archive);

        // Check if archive file exists
        $this->assertFileExists($archive->getFilePath());

        // Create archive
        $archiveHandlerExtract = new ArchiveHandler($localModuleLoader, $modulesExtractedRootPath);

        // Extract archive
        $archiveHandlerExtract->extract($archive);

        // Check if module directory exists
        $modulePath = $modulesExtractedRootPath . '/' . $archiveName . '/' . $version;
        $this->assertDirectoryExists($modulePath);
    }
}
