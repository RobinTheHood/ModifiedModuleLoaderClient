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

namespace RobinTheHood\ModifiedModuleLoaderClient\Cli\Command;

use RobinTheHood\ModifiedModuleLoaderClient\Cli\MmlcCli;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\TextRenderer;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\RemoteModuleLoader;

class CommandList implements CommandInterface
{
    public function __construct()
    {
    }

    public function getName(): string
    {
        return 'list';
    }

    public function run(MmlcCli $cli): void
    {
        $remoteModuleLoader = RemoteModuleLoader::create();
        $modules = $remoteModuleLoader->loadAllLatestVersions();
        foreach ($modules as $module) {
            echo $module->getArchiveName() . "\n";
        }
    }

    public function runHelp(MmlcCli $cli): void
    {
        TextRenderer::renderHelpHeading('Description:');
        echo "  List all available modules that can be used with MMLC.\n";
        echo "\n";

        TextRenderer::renderHelpHeading('Usage:');
        echo "  list\n";
        echo "\n";

        TextRenderer::renderHelpHeading('Options:');
        TextRenderer::renderHelpOption('h', 'help', 'Display help for the given command.');
        echo "\n";

        echo "Read more at https://module-loader.de/documentation.php\n";
    }
}
