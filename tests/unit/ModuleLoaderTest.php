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
use RobinTheHood\ModifiedModuleLoaderClient\Loader\ModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;

class ModuleLoaderTest extends TestCase
{
    private $loader;

    protected function setUp(): void
    {
        $this->loader = ModuleLoader::create(Comparator::CARET_MODE_STRICT);
    }

    public function testCanLoadAllVersionsByContraintWithLocalAuto()
    {
        $modules = $this->loader->loadAllByArchiveNameAndConstraint('robinthehood/modified-std-module', '1.0.0');
        $this->assertEquals(0, count($modules));

        $modules = $this->loader->loadAllByArchiveNameAndConstraint('robinthehood/modified-std-module', '^99.0.0');

        if (count($modules) != 1 || $modules[0]->getVersion() != 'auto') {
            return;
        }

        $this->assertEquals(1, count($modules)); // Lokale Version mit 'auto';

        $modules = $this->loader->loadAllByArchiveNameAndConstraint('robinthehood/modified-std-module', '^0.2.0');
        $this->assertEquals('auto', $modules[0]->getVersion());  // Lokale Version mit 'auto';
        $this->assertEquals('0.2.0', $modules[1]->getVersion());
        $this->assertEquals('0.3.0', $modules[2]->getVersion());
        $this->assertEquals('0.4.0', $modules[3]->getVersion());

        $modules = $this->loader->loadAllByArchiveNameAndConstraint('robinthehood/modified-std-module', '^0.0.0');
        $this->assertEquals('auto', $modules[0]->getVersion());  // Lokale Version mit 'auto';
        $this->assertEquals('0.0.1', $modules[1]->getVersion());
        $this->assertEquals('0.1.0', $modules[2]->getVersion());
    }

    public function testCanLoadAllVersionsByContraint()
    {
        $modules = $this->loader->loadAllByArchiveNameAndConstraint('robinthehood/modified-std-module', '1.0.0');
        $this->assertEquals(0, count($modules));

        $modules = $this->loader->loadAllByArchiveNameAndConstraint('robinthehood/modified-std-module', '^99.0.0');

        if (count($modules) == 1) {
            return;
        }

        $this->assertEquals(0, count($modules)); // Lokale Version mit 'auto';

        // tests for version <1.0.0
        $modules = $this->loader->loadAllByArchiveNameAndConstraint('robinthehood/modified-std-module', '^0.2.0');
        $this->assertEquals('0.2.0', $modules[0]->getVersion());

        $modules = $this->loader->loadAllByArchiveNameAndConstraint('robinthehood/modified-std-module', '^0.0.1');
        $this->assertEquals('0.0.1', $modules[0]->getVersion());

        $modules = $this->loader->loadAllByArchiveNameAndConstraint('robinthehood/modified-std-module', '^0.6.0');
        $this->assertEquals(4, count($modules));
        $this->assertEquals('0.6.0', $modules[0]->getVersion());
        $this->assertEquals('0.6.1', $modules[1]->getVersion());
        $this->assertEquals('0.6.2', $modules[2]->getVersion());

        // tests for version >=1.0.0
        $modules = $this->loader->loadAllByArchiveNameAndConstraint('composer/autoload', '^1.2.0');
        $this->assertEquals(5, count($modules));
        $this->assertEquals('1.2.0', $modules[0]->getVersion());
        $this->assertEquals('1.2.1', $modules[1]->getVersion());
        $this->assertEquals('1.2.2', $modules[2]->getVersion());
        $this->assertEquals('1.3.0', $modules[3]->getVersion());
        $this->assertEquals('1.4.0', $modules[4]->getVersion());
    }
}
