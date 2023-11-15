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

use Exception;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\MmlcCli;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\ModuleManagerFactory;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\TextRenderer;
use RuntimeException;

class CommandInstall implements CommandInterface
{
    public function __construct()
    {
    }

    public function getName(): string
    {
        return 'install';
    }

    public function run(MmlcCli $cli): void
    {
        $archiveName = $cli->getFilteredArgument(0);

        $parts = explode(':', $archiveName);
        if (count($parts) === 2) {
            $archiveName = $parts[0] ?? '';
            $versionConstraint = $parts[1] ?? '';
        } elseif (count($parts) === 1) {
            $archiveName = $parts[0] ?? '';
            $versionConstraint = '>0.0.0';
        } else {
            $archiveName = '';
            $versionConstraint = '';
        }

        if (!$archiveName) {
            $cli->writeLine($this->getHelp($cli));
            return;
        }

        $moduleManager = ModuleManagerFactory::create($cli);
        $result = $moduleManager->install($archiveName, $versionConstraint);

        // try {
        //     $moduleManager = ModuleManagerFactory::create($cli);
        //     $moduleManager->install($archiveName, $versionConstraint);
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
            . "  Download and install a module in your shop.\n"
            . "\n"

            . TextRenderer::renderHelpHeading('Usage:')
            . "  install <archiveName>\n"
            . "\n"

            . TextRenderer::renderHelpHeading('Arguments:')
            . TextRenderer::renderHelpArgument('archiveName', 'The archiveName of the module to be loaded.')
            . "\n"

            . TextRenderer::renderHelpHeading('Options:')
            . TextRenderer::renderHelpOption('h', 'help', 'Display help for the given command.')
            . "\n"

            . "Read more at https://module-loader.de/documentation.php";
    }
}
