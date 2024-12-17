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
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFilter;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;

class RemoteModuleLoaderTest extends TestCase
{
    private $loader;

    /** @var ModuleFilter */
    private $moduleFilter;

    protected function setUp(): void
    {
        $this->loader = RemoteModuleLoader::create();
        $this->moduleFilter = ModuleFilter::create(Comparator::CARET_MODE_STRICT);
    }

    public function testCanLoadAllVersions()
    {
        $modules = $this->loader->loadAllVersions();

        $this->assertContainsOnlyInstancesOf(Module::class, $modules);
        $this->assertGreaterThan(150, count($modules));

        $filteredModules = $this->moduleFilter->filterByArchiveName($modules, 'composer/autoload');
        $this->assertGreaterThan(1, count($filteredModules));

        $filteredModules = $this->moduleFilter->filterByArchiveName($modules, 'robinthehood/modified-std-module');
        $this->assertGreaterThan(2, count($filteredModules));
    }

    public function testCanLoadAllLatestVersions()
    {
        $modules = $this->loader->loadAllLatestVersions();

        $this->assertContainsOnlyInstancesOf(Module::class, $modules);
        $this->assertGreaterThan(30, count($modules));

        $filteredModules = $this->moduleFilter->filterNewestVersion($modules);
        $this->assertEquals(count($filteredModules), count($modules));

        $filteredModules = $this->moduleFilter->filterByArchiveName($modules, 'robinthehood/modified-std-module');
        $this->assertEquals(1, count($filteredModules));
    }

    public function testCanLoadAllVersionsByArchiveName()
    {
        $modules = $this->loader->loadAllVersionsByArchiveName('robinthehood/modified-std-module');
        $this->assertGreaterThan(2, count($modules));

        $module = $modules[0];
        $this->assertEquals('robinthehood/modified-std-module', $module->getArchiveName());
    }

    public function testCanLoadLatestVersionByArchiveName()
    {
        $module = $this->loader->loadLatestVersionByArchiveName('composer/autoload');

        $this->assertEquals('composer/autoload', $module->getArchiveName());
        $this->assertEquals('1.5.0', $module->getVersion());
    }

    public function testCanLoadByArchiveNameAndVersion()
    {
        $module = $this->loader->loadByArchiveNameAndVersion('composer/autoload', '1.1.0');

        $this->assertEquals('composer/autoload', $module->getArchiveName());
        $this->assertEquals('1.1.0', $module->getVersion());
    }
}
