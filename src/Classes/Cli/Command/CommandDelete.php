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
use RobinTheHood\ModifiedModuleLoaderClient\Cli\ModuleManagerFactory;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\TextRenderer;
use RuntimeException;

class CommandDelete implements CommandInterface
{
    public function __construct()
    {
    }

    public function getName(): string
    {
        return 'delete';
    }

    public function run(MmlcCli $cli): void
    {
        $archiveName = $cli->getFilteredArgument(0);
        $version = $cli->getFilteredArgument(1);

        if (!$archiveName) {
            $cli->writeLine($this->getHelp($cli));
            return;
        }

        if (!$version) {
            $cli->writeLine($this->getHelp($cli));
            return;
        }

        $moduleManager = ModuleManagerFactory::create($cli);
        $result = $moduleManager->delete($archiveName, $version);

        // try {
        //     $moduleManager = ModuleManagerFactory::create($cli);
        //     $moduleManager->delete($archiveName, $version);
        // } catch (RuntimeException $e) {
        //     $cli->writeLine(TextRenderer::color('Exception:', TextRenderer::COLOR_RED) . ' ' . $e->getMessage());
        //     die();
        // }

        $cli->writeLine(TextRenderer::color('ready', TextRenderer::COLOR_GREEN));
        return;
    }

    public function getHelp(MmlcCli $cli): string
    {
        return
            TextRenderer::renderHelpHeading('Description:')
            . "  Delete a loaded uninstalled module.\n"
            . "\n"

            . TextRenderer::renderHelpHeading('Usage:')
            . "  delete <archiveName> <version>\n"
            . "\n"

            . TextRenderer::renderHelpHeading('Arguments:')
            . TextRenderer::renderHelpArgument('archiveName', 'The archiveName of the module to be loaded.')
            . TextRenderer::renderHelpArgument('version', 'The version of the module to be loaded.')
            . "\n"

            . TextRenderer::renderHelpHeading('Options:')
            . TextRenderer::renderHelpOption('h', 'help', 'Display help for the given command.')
            . "\n"

            . "Read more at https://module-loader.de/documentation.php";
    }
}
