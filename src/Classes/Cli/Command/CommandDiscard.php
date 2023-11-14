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

class CommandDiscard implements CommandInterface
{
    public function __construct()
    {
    }

    public function getName(): string
    {
        return 'discard';
    }

    public function run(MmlcCli $cli): void
    {
        $archiveName = $cli->getFilteredArgument(0);

        if (!$archiveName) {
            $cli->writeLine($this->getHelp($cli));
            return;
        }

        try {
            $moduleManager = ModuleManagerFactory::create($cli);
            $moduleManager->discard($archiveName);
        } catch (RuntimeException $e) {
            $cli->writeLine(TextRenderer::color('Exception:', TextRenderer::COLOR_RED) . ' ' . $e->getMessage());
            die();
        }

        $cli->writeLine(TextRenderer::color('ready', TextRenderer::COLOR_GREEN));
        return;
    }

    public function getHelp(MmlcCli $cli): string
    {
        return
            TextRenderer::renderHelpHeading('Description:')
            . "  Discarding a changed installed module.\n"
            . "\n"

            . TextRenderer::renderHelpHeading('Usage:')
            . "  discard <archiveName>\n"
            . "\n"

            . TextRenderer::renderHelpHeading('Options:')
            // . TextRenderer::renderHelpOption('f', 'force', 'Uninstall even if the module has changes.')
            . TextRenderer::renderHelpOption('h', 'help', 'Display help for the given command.')
            . "\n"

            . "Read more at https://module-loader.de/documentation.php";
    }
}
