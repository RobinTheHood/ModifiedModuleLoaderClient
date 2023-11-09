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
use RobinTheHood\ModifiedModuleLoaderClient\Cli\TextRenderer;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\ModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleManager\ModuleManager;
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

        $moduleManager = ModuleManager::createFromConfig();
        $moduleManager->install($archiveName, $versionConstraint);
        die();


        $moduleLoader = ModuleLoader::createFromConfig();

        if ($archiveName && $versionConstraint) {
            // TODO: Zusammen mit dem dependency manager ein mögliches Modul finden
            $module = $moduleLoader->loadLatestByArchiveNameAndConstraint($archiveName, $versionConstraint);
        } else {
            $module = $moduleLoader->loadLatestVersionByArchiveName($archiveName);
        }

        if (!$module) {
            $cli->writeLine(
                "Module " . TextRenderer::color($archiveName, TextRenderer::COLOR_GREEN) . " not found."
            );
            return;
        }

        if ($module->isLoaded()) {
            $loadedModule = $module;
        } else {
            $loadedModule = $this->download($cli, $module);
        }

        $this->install($cli, $loadedModule);

        $cli->writeLine(TextRenderer::color('ready', TextRenderer::COLOR_GREEN));
        return;
    }

    private function download(MmlcCli $cli, Module $module): Module
    {
        $moduleText =
            "module " . TextRenderer::color($module->getArchiveName(), TextRenderer::COLOR_GREEN)
            . " version " . TextRenderer::color($module->getVersion(), TextRenderer::COLOR_YELLOW);

        $cli->writeLine("Download $moduleText ...");

        try {
            $moduleManager = ModuleManager::createFromConfig();
            return $moduleManager->pull($module);
        } catch (RuntimeException $e) {
            $cli->writeLine(
                TextRenderer::color('Error:', TextRenderer::COLOR_RED)
                . " can not download $moduleText."
                . " Message: " . $e->getMessage()
            );
            die();
        }
    }

    private function install(MmlcCli $cli, Module $module): void
    {
        $moduleText =
            "module " . TextRenderer::color($module->getArchiveName(), TextRenderer::COLOR_GREEN)
            . " version " . TextRenderer::color($module->getVersion(), TextRenderer::COLOR_YELLOW);

        $cli->writeLine("Installing $moduleText ...");

        try {
            $moduleManager = ModuleManager::createFromConfig();
            $moduleManager->install($module);
        } catch (Exception $e) {
            $cli->writeLine(
                TextRenderer::color('Error:', TextRenderer::COLOR_RED)
                . " can not install $moduleText."
                . "\nMessage: " . $e->getMessage()
            );
            die();
        }
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
