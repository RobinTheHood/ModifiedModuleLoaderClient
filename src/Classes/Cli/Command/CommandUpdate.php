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
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleManager\ModuleManager;
use RuntimeException;

class CommandUpdate implements CommandInterface
{
    public function __construct()
    {
    }

    public function getName(): string
    {
        return 'update';
    }

    public function run(MmlcCli $cli): void
    {
        $archiveName = $cli->getFilteredArgument(0);

        if (!$archiveName) {
            $cli->writeLine($this->getHelp($cli));
            return;
        }

        $moduleLoader = LocalModuleLoader::createFromConfig();
        $module = $moduleLoader->loadInstalledVersionByArchiveName($archiveName);

        if (!$module) {
            $cli->writeLine(
                "Module " . TextRenderer::color($archiveName, TextRenderer::COLOR_GREEN) . " is not installed."
            );
            return;
        }

        $moduleText =
            "module " . TextRenderer::color($archiveName, TextRenderer::COLOR_GREEN)
            . " version " . TextRenderer::color($module->getVersion(), TextRenderer::COLOR_YELLOW);

        // if ($module->is()) {
        //     $cli->writeLine("Can not update $moduleText because it is installed.");
        //     return;
        // }

        $cli->writeLine("Update $moduleText ...");

        try {
            $moduleManager = ModuleManager::createFromConfig();
            $newModule = $moduleManager->update($module);
        } catch (RuntimeException $e) {
            $cli->writeLine(
                TextRenderer::color('Error:', TextRenderer::COLOR_RED)
                . " can not delete $moduleText."
                . " Message: " . $e->getMessage()
            );
            die();
        }

        $newModulText =
            "module " . TextRenderer::color($newModule->getArchiveName(), TextRenderer::COLOR_GREEN)
            . " version " . TextRenderer::color($newModule->getVersion(), TextRenderer::COLOR_YELLOW);

        $cli->writeLine("Updated $moduleText to $newModulText.");

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
