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

namespace RobinTheHood\ModifiedModuleLoaderClient\Cli;

use RobinTheHood\ModifiedModuleLoaderClient\Loader\RemoteModuleLoader;

class CommandInfo
{
    public function __construct()
    {
    }

    public function run($archiveName): void
    {
        if (!$archiveName) {
            echo "No archiveName specified. \n";
            return;
        }

        $remoteModuleLoader = RemoteModuleLoader::create();
        $module = $remoteModuleLoader->loadLatestVersionByArchiveName($archiveName);

        if (!$module) {
            echo "Module $archiveName not found. \n";
            return;
        }

        echo "archiveName:       " . $module->getArchiveName() . "\n";
        echo "latestVersion:     " . $module->getVersion() . "\n";
        echo "moduleName:        " . $module->getName() . "\n";
        echo "shortDescription:  " . $module->getShortDescription() . "\n";
    }
}
