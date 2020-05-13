<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Test\TestCase\Classes;

use PHPUnit\Framework\TestCase;

use RobinTheHood\ModifiedModuleLoaderClient\App;

class AppTest extends TestCase
{

    public function testRootDirectory()
    {
        $this->assertStringEndsWith('ModifiedModuleLoaderClient', App::getRoot());
        $this->assertStringEndsNotWith('ModifiedModuleLoaderClient/', App::getRoot());
    }

    public function testSrcRootDirectory()
    {
        $this->assertStringEndsWith('ModifiedModuleLoaderClient/src', App::getSrcRoot());
        $this->assertStringEndsNotWith('ModifiedModuleLoaderClient/src/', App::getSrcRoot());
    }

    public function testTemplatesRootDirectory()
    {
        $this->assertStringEndsWith('ModifiedModuleLoaderClient/src/Templates', App::getTemplatesRoot());
        $this->assertStringEndsNotWith('ModifiedModuleLoaderClient/src/Templates/', App::getTemplatesRoot());
    }

    public function testArchivesRootDirectory()
    {
        $this->assertStringEndsWith('ModifiedModuleLoaderClient/Archives', App::getArchivesRoot());
        $this->assertStringEndsNotWith('ModifiedModuleLoaderClient/Archives/', App::getArchivesRoot());
    }

    public function testModulesRootDirectory()
    {
        $this->assertStringEndsWith('ModifiedModuleLoaderClient/Modules', App::getModulesRoot());
        $this->assertStringEndsNotWith('ModifiedModuleLoaderClient/Modules/', App::getModulesRoot());
    }

}
