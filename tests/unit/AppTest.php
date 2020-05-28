<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use RobinTheHood\ModifiedModuleLoaderClient\App;

class AppTest extends TestCase
{

    public function testRootDirectoryEndsWidthModifiedModuleLoaderClient()
    {
        $this->assertStringEndsWith('ModifiedModuleLoaderClient', App::getRoot());
    }

    public function testRootDirectoryEndsNotWidthSlash()
    {
        $this->assertStringEndsNotWith('ModifiedModuleLoaderClient/', App::getRoot());
    }

    public function testSrcRootDirectoryHasRightPath()
    {
        $this->assertStringEndsWith('ModifiedModuleLoaderClient/src', App::getSrcRoot());
    }

    public function testSrcRootDirectoryEndsNotWidthSlash()
    {
        $this->assertStringEndsNotWith('ModifiedModuleLoaderClient/src/', App::getSrcRoot());
    }

    public function testTemplatesRootDirectoryHasRightPath()
    {
        $this->assertStringEndsWith('ModifiedModuleLoaderClient/src/Templates', App::getTemplatesRoot());
    }

    public function testTemplatesRootDirectoryEndsNotWidthSlash()
    {
        $this->assertStringEndsNotWith('ModifiedModuleLoaderClient/src/Templates/', App::getTemplatesRoot());
    }

    public function testArchivesRootDirectoryHasRightPath()
    {
        $this->assertStringEndsWith('ModifiedModuleLoaderClient/Archives', App::getArchivesRoot());
    }

    public function testArchivesRootDirectoryEndsNotWidthSlash()
    {
        $this->assertStringEndsNotWith('ModifiedModuleLoaderClient/Archives/', App::getArchivesRoot());
    }

    public function testModulesRootDirectoryHasRightPath()
    {
        $this->assertStringEndsWith('ModifiedModuleLoaderClient/Modules', App::getModulesRoot());
    }

    public function testModulesRootDirectoryEndsNotWidthSlash()
    {
        $this->assertStringEndsNotWith('ModifiedModuleLoaderClient/Modules/', App::getModulesRoot());
    }

}
