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
use RobinTheHood\ModifiedModuleLoaderClient\Archive\ArchiveName;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Version;

class ArchiveTest extends TestCase
{
    public function testCreate(): void
    {
        $archiveName = 'robinthehood/modified-std-module';
        $version = '1.0.0';
        $archivesRootPath = '/path/to/archives';

        $archive = Archive::create($archiveName, $version, $archivesRootPath);

        $this->assertInstanceOf(Archive::class, $archive);
        $this->assertInstanceOf(ArchiveName::class, $archive->getArchiveName());
        $this->assertInstanceOf(Version::class, $archive->getVersion());
        $this->assertSame($archiveName, $archive->getArchiveName()->__toString());
        $this->assertSame($version, $archive->getVersion()->__toString());
        $this->assertSame($archivesRootPath, $archive->getArchivesRootPath());
    }

    public function testGetFileName(): void
    {
        $archiveName = new ArchiveName('robinthehood/modified-std-module');
        $version = new Version(1, 2, 3);
        $archivesRootPath = '/path/to/archives';

        $archive = new Archive($archiveName, $version, $archivesRootPath);

        $expectedFileName = 'robinthehood_modified-std-module_1.2.3.tar';
        $this->assertSame($expectedFileName, $archive->getFileName());
    }

    public function testGetFilePath(): void
    {
        $archiveName = new ArchiveName('robinthehood/modified-std-module');
        $version = new Version(1, 2, 3);
        $archivesRootPath = '/path/to/archives';

        $archive = new Archive($archiveName, $version, $archivesRootPath);

        $expectedFilePath = '/path/to/archives/robinthehood_modified-std-module_1.2.3.tar';
        $this->assertSame($expectedFilePath, $archive->getFilePath());
    }
}
