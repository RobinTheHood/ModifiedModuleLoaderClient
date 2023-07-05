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
use RobinTheHood\ModifiedModuleLoaderClient\Archive\ArchiveName;

class ArchiveNameTest extends TestCase
{
    public function testConstructorValidArchiveName(): void
    {
        $archiveName = new ArchiveName('vendor/module');

        $this->assertInstanceOf(ArchiveName::class, $archiveName);
        $this->assertSame('module', $archiveName->getModuleName());
        $this->assertSame('vendor', $archiveName->getVendorName());
        $this->assertSame('vendor/module', $archiveName->__toString());
    }

    public function testConstructorInvalidArchiveName1(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ArchiveName('invalid');
    }

    public function testConstructorInvalidArchiveName2(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ArchiveName('invalid/invalid/');
    }

    public function testConstructorInvalidArchiveName3(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ArchiveName('in.valid/invalid');
    }
}
