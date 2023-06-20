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

namespace RobinTheHood\ModifiedModuleLoaderClient\DependencyManager;

use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\ShopInfo;

class SystemSetFactory
{
    /** @var LocalModuleLoader */
    private $localModuleLoader;

    public static function create(int $mode): SystemSetFactory
    {
        $localModuleLoader = LocalModuleLoader::create($mode);
        $localModuleLoader = new SystemSetFactory($localModuleLoader);
        return $localModuleLoader;
    }

    public function __construct(LocalModuleLoader $localModuleLoader)
    {
        $this->localModuleLoader = $localModuleLoader;
    }

    public function getSystemSet(): SystemSet
    {
        $systemSet = new SystemSet();
        $systemSet->add('modified', ShopInfo::getModifiedVersion());
        $systemSet->add('php', phpversion());
        $systemSet->add('mmlc', App::getMmlcVersion());

        $modules = $this->localModuleLoader->loadAllInstalledVersions();
        foreach ($modules as $module) {
            $systemSet->add($module->getArchiveName(), $module->getVersion());
        }
        return $systemSet;
    }
}
