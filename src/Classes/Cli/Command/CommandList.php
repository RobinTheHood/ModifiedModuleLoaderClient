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
        if ($cli->hasOption('--installed') || $cli->hasOption('-i')) {
            $this->listInstalledVersions($cli);
        } else {
            $this->listRemoteModules($cli);
        }

        return;
    }

    private function listInstalledVersions(MmlcCli $cli): void
    {
        $localModuleLoader = LocalModuleLoader::createFromConfig();
        $modules = $localModuleLoader->loadAllInstalledVersions();

        foreach ($modules as $module) {
            $cli->writeLine(
                TextRenderer::rightPad($module->getArchiveName(), 40) . " "
                . TextRenderer::rightPad($module->getVersion(), 10) . " "
                . ($module->isChanged() ? 'changed' : '')
            );
        }
    }

    private function listRemoteModules(MmlcCli $cli): void
    {
        // $moduleLoader = ModuleLoader::createFromConfig();
        // $modules = $moduleLoader->loadAllVersionsWithLatestRemote();
        // $moduleFilter = ModuleFilter::createFromConfig();
        // $modules = $moduleFilter->filterNewestOrInstalledVersion($modules);

        $remoteModuleLoader = RemoteModuleLoader::create();
        $modules = $remoteModuleLoader->loadAllLatestVersions();

        if ($cli->hasOption('--downloadable') || $cli->hasOption('-d')) {
            foreach ($modules as $module) {
                if ($module->isLoadable()) {
                    $cli->writeLine($module->getArchiveName());
                }
            }
        } else {
            foreach ($modules as $module) {
                $cli->writeLine($module->getArchiveName());
            }
        }
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
            . TextRenderer::renderHelpOption('i', 'installed', 'Only show modules that are installed.')
            . TextRenderer::renderHelpOption('d', 'downloadable', 'Only show modules that are downloadable.')
            . TextRenderer::renderHelpOption('h', 'help', 'Display help for the given command.')
            . "\n"

            . "Read more at https://module-loader.de/documentation.php";
    }
}
