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
use RobinTheHood\ModifiedModuleLoaderClient\Loader\ModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleInstaller;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleManager\ModuleManager;
use RuntimeException;

class CommandDownload implements CommandInterface
{
    public function __construct()
    {
    }

    public function getName(): string
    {
        return 'download';
    }

    public function run(MmlcCli $cli): void
    {
        $archiveName = $cli->getFilteredArgument(0);

        if (!$archiveName) {
            $cli->writeLine($this->getHelp($cli));
            return;
        }

        $moduleLoader = ModuleLoader::createFromConfig();
        $module = $moduleLoader->loadLatestVersionByArchiveName($archiveName);

        if (!$module) {
            $cli->writeLine("Module " . TextRenderer::color($archiveName, TextRenderer::COLOR_GREEN) . " not found.");
            return;
        }

        $moduleText =
            "module " . TextRenderer::color($archiveName, TextRenderer::COLOR_GREEN)
            . " version " . TextRenderer::color($module->getVersion(), TextRenderer::COLOR_YELLOW);

        if ($module->isLoaded()) {
            $cli->writeLine("Can not download $moduleText, because is it already downloaded.");
            return;
        }

        $cli->writeLine("Download $moduleText ...");

        try {
            $moduleManager = ModuleManager::createFromConfig();
            $moduleManager->pull($module);
        } catch (RuntimeException $e) {
            $cli->writeLine(
                TextRenderer::color('Error:', TextRenderer::COLOR_RED)
                . " can not download $moduleText."
                . " Message: " . $e->getMessage()
            );
            return;
        }

        $cli->writeLine(TextRenderer::color('ready', TextRenderer::COLOR_GREEN));
        return;
    }

    public function getHelp(MmlcCli $cli): string
    {
        return
            TextRenderer::renderHelpHeading('Description:')
            . "  Downloads a available MMLC Module from the Internet.\n"
            . "\n"

            . TextRenderer::renderHelpHeading('Usage:')
            . "  download <archiveName> []\n"
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
