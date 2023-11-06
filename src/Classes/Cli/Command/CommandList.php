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
            $cli->writeLine($module->getArchiveName());
        }

        return;
    }

    public function getHelp(MmlcCli $cli): string
    {
        return
            TextRenderer::renderHelpHeading('Description:')
            . "  List all available modules that can be used with MMLC.\n"
            . "\n"

            . TextRenderer::renderHelpHeading('Usage:')
            . "  list\n"
            . "\n"

            . TextRenderer::renderHelpHeading('Options:')
            . TextRenderer::renderHelpOption('h', 'help', 'Display help for the given command.')
            . "\n"

            . "Read more at https://module-loader.de/documentation.php";
    }
}
