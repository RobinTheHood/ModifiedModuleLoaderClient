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
            $this->runHelp($cli);
            return;
        }

        $moduleLoader = ModuleLoader::createFromConfig();
        $module = $moduleLoader->loadLatestVersionByArchiveName($archiveName);

        if (!$module) {
            echo "Module " . TextRenderer::color($archiveName, TextRenderer::COLOR_GREEN) . " not found.\n";
            return;
        }

        if ($module->isLoaded()) {
            echo "Module " . TextRenderer::color($archiveName, TextRenderer::COLOR_GREEN)
                . " version " . TextRenderer::color($module->getVersion(), TextRenderer::COLOR_YELLOW)
                . " is already downloaded.\n";
            return;
        }

        echo "Download module " . TextRenderer::color($archiveName, TextRenderer::COLOR_GREEN)
            . " version " . TextRenderer::color($module->getVersion(), TextRenderer::COLOR_YELLOW)
            . " ...\n";

        $moduleInstaller = ModuleInstaller::createFromConfig();

        if (!$moduleInstaller->pull($module)) {
            echo TextRenderer::color('Error:', TextRenderer::COLOR_RED)
                . " the module $archiveName could not be loaded. \n";
        }

        echo TextRenderer::color('ready', TextRenderer::COLOR_GREEN) . "\n";
    }

    public function runHelp(MmlcCli $cli): void
    {
        TextRenderer::renderHelpHeading('Description:');
        echo "  Downloads a available MMLC Module from the Internet.\n";
        echo "\n";

        TextRenderer::renderHelpHeading('Usage:');
        echo "  download <archiveName>\n";
        echo "\n";

        TextRenderer::renderHelpHeading('Arguments:');
        TextRenderer::renderHelpArgument('archiveName', 'The archiveName of the module to be loaded.');
        echo "\n";

        TextRenderer::renderHelpHeading('Options:');
        TextRenderer::renderHelpOption('h', 'help', 'Display help for the given command.');
        echo "\n";

        echo "Read more at https://module-loader.de/documentation.php\n";
    }
}
