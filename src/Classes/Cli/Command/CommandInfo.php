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

class CommandInfo implements CommandInterface
{
    public function __construct()
    {
    }

    public function getName(): string
    {
        return 'info';
    }

    public function run(MmlcCli $cli): void
    {
        $archiveName = $cli->getFilteredArgument(0);
        if (!$archiveName) {
            $cli->writeLine("No archiveName specified.");
            return;
        }

        $remoteModuleLoader = RemoteModuleLoader::create();
        $module = $remoteModuleLoader->loadLatestVersionByArchiveName($archiveName);

        if (!$module) {
            $cli->writeLine("Module $archiveName not found.");
            return;
        }

        $cli->writeLine("name:              " . $module->getName());
        $cli->writeLine("archiveName:       " . $module->getArchiveName());
        $cli->writeLine("latestVersion:     " . $module->getVersion());
        $cli->writeLine("shortDescription:  " . $module->getShortDescription());

        return;
    }

    public function getHelp(MmlcCli $cli): string
    {
        return
            TextRenderer::renderHelpHeading('Description:')
            . "  Display information and details for a specific module.\n"
            . "\n"

            . TextRenderer::renderHelpHeading('Usage:')
            . "  info <archiveName>\n"
            . "\n"

            . TextRenderer::renderHelpHeading('Options:')
            . TextRenderer::renderHelpOption('h', 'help', 'Display help for the given command.')
            . "\n"

            . "Read more at https://module-loader.de/documentation.php\n";
    }
}
