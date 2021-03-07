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
use RobinTheHood\ModifiedModuleLoaderClient\Loader\RemoteModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Api\Client\ApiRequest;
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFilter;

class RemoteModuleLoaderTest extends TestCase
{
    public function setUp()
    {
        //require_once 'config/config.php';
        $this->loader = new RemoteModuleLoader();
    }

    public function testCanLoadAllVersions()
    {
        $apiRequest = new ApiRequest();
        $modules = $this->loader->loadAllVersions();

        $this->assertContainsOnlyInstancesOf(Module::class, $modules);
        $this->assertGreaterThan(150, count($modules));

        $filteredModules = ModuleFilter::filterByArchiveName($modules, 'composer/autoload');
        $this->assertGreaterThan(1, count($filteredModules));

        $filteredModules = ModuleFilter::filterByArchiveName($modules, 'robinthehood/modified-std-module');
        $this->assertGreaterThan(2, count($filteredModules));
    }

    public function testCanLoadAllLatestVersions()
    {
        $apiRequest = new ApiRequest();
        $modules = $this->loader->loadAllLatestVersions();

        $this->assertContainsOnlyInstancesOf(Module::class, $modules);
        $this->assertGreaterThan(30, count($modules));

        $filteredModules = ModuleFilter::filterNewestVersion($modules);
        $this->assertEquals(count($filteredModules), count($modules));

        $filteredModules = ModuleFilter::filterByArchiveName($modules, 'robinthehood/modified-std-module');
        $this->assertEquals(1, count($filteredModules));
    }

    public function testCanLoadAllVersionsByArchiveName()
    {
        $apiRequest = new ApiRequest();
        $modules = $this->loader->loadAllVersionsByArchiveName('robinthehood/modified-std-module');
        $this->assertGreaterThan(2, count($modules));

        $module = $modules[0];
        $this->assertEquals('robinthehood/modified-std-module', $module->getArchiveName());
    }

    public function testCanLoadLatestVersionByArchiveName()
    {
        $apiRequest = new ApiRequest();
        $module = $this->loader->loadLatestVersionByArchiveName('composer/autoload');

        $this->assertEquals('composer/autoload', $module->getArchiveName());
        $this->assertEquals('1.2.0', $module->getVersion());
    }

    public function testCanLoadByArchiveNameAndVersion()
    {
        $apiRequest = new ApiRequest();
        $module = $this->loader->loadLatestVersionByArchiveName('composer/autoload', '1.1.0');

        $this->assertEquals('composer/autoload', $module->getArchiveName());
        $this->assertEquals('1.2.0', $module->getVersion());
    }
}
