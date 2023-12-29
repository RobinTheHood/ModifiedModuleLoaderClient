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

namespace RobinTheHood\ModifiedModuleLoaderClient\Cli;

use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\Command\CommandCreate;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\Command\CommandDelete;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\Command\CommandDiscard;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\Command\CommandDownload;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\Command\CommandInfo;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\Command\CommandInstall;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\Command\CommandList;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\Command\CommandUninstall;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\Command\CommandUpdate;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\Command\CommandWatch;

class MmlcCli extends Cli
{
    public function __construct()
    {
        $this->addCommand(new CommandCreate());
        $this->addCommand(new CommandDownload());
        $this->addCommand(new CommandInstall());
        $this->addCommand(new CommandUpdate());
        $this->addCommand(new CommandUninstall());
        $this->addCommand(new CommandDiscard());
        $this->addCommand(new CommandDelete());
        $this->addCommand(new CommandList());
        $this->addCommand(new CommandInfo());
        $this->addCommand(new CommandWatch());
    }

    public function run()
    {
        $command = $this->getCommand();

        if (!$command && ($this->hasOption('--version') || $this->hasOption('-v'))) {
            $this->writeLine($this->renderVersion());
        } elseif ($command && ($this->hasOption('--help') || $this->hasOption('-h'))) {
            $this->writeLine($command->getHelp($this));
            return;
        } elseif ($command) {
            $command->run($this);
            return;
        } else {
            $this->getHelp();
        }
    }

    private function getHelp()
    {
        $this->writeLine(
            $this->renderLogo()
            . "\n"

            . $this->renderVersion()
            . "\n"

            . TextRenderer::renderHelpHeading('Usage:')
            . "  command [options]\n"
            . "\n"

            . TextRenderer::renderHelpHeading('Options:')
            . TextRenderer::renderHelpOption('h', 'help', 'Display help for the given command.')
            . TextRenderer::renderHelpOption('v', 'version', 'Display this application version.')
            . "\n"

            . TextRenderer::renderHelpHeading('Commands:')
            . TextRenderer::renderHelpCommand('download', 'Download the latest version of module.')
            . TextRenderer::renderHelpCommand('install', 'Download and install a module in your shop. Use the -f or --force option to enforce.')
            . TextRenderer::renderHelpCommand('update', 'Update an already installed module to the latest version. Use the -f or --force option to enforce.')
            . TextRenderer::renderHelpCommand('uninstall', 'Uninstall a module from your shop. Use the -f or --force option to enforce.')
            . TextRenderer::renderHelpCommand('list', 'List all available modules that can be used with MMLC.')
            . TextRenderer::renderHelpCommand('search', 'Search for modules based on a specific search term.')
            . TextRenderer::renderHelpCommand('info', 'Display information and details for a specific module.')
            . TextRenderer::renderHelpCommand('status', 'Show the status of all installed modules in MMLC.')
            . TextRenderer::renderHelpCommand('create', 'Create a new module in MMLC. Use the -i option for the interactive mode.')
            . TextRenderer::renderHelpCommand('watch', 'Automatically detect and apply file changes for module development.')
            . TextRenderer::renderHelpCommand('discard', 'Discard changes to a module. Use the -f or --force option to enforce.')
            . TextRenderer::renderHelpCommand('self-update', 'Updates MMLC to the latest version.')
            . "\n"

            . "Read more at https://module-loader.de/documentation.php"
        );
    }

    public function renderLogo(): string
    {
        // return ''
        //     . "    __  _____  _____    ______   ________    ____\n"
        //     . "   /  |/  /  |/  / /   / ____/  / ____/ /   /  _/\n"
        //     . "  / /|_/ / /|_/ / /   / /      / /   / /    / /  \n"
        //     . " / /  / / /  / / /___/ /___   / /___/ /____/ /   \n"
        //     . "/_/  /_/_/  /_/_____/\____/   \____/_____/___/   \n";
        // created with: https://patorjk.com/software/taag/#p=display&f=Slant&t=MMLC%20CLI

        return ''
            . "    __  ___ __  ___ __    ______   ______ __     ____\n"
            . "   /  |/  //  |/  // /   / ____/  / ____// /    /  _/\n"
            . "  / /|_/ // /|_/ // /   / /      / /    / /     / /  \n"
            . " / /  / // /  / // /___/ /___   / /___ / /___ _/ /   \n"
            . "/_/  /_//_/  /_//_____/\____/   \____//_____//___/   \n";
        // cretated with: https://patorjk.com/software/taag/#p=display&h=1&f=Slant&t=MMLC%20CLI
    }

    private function renderVersion(): string
    {
        $mmlcVersion = App::getMmlcVersion();
        $gitBranch = $this->getCurrentGitBranch(App::getRoot() . '/.git');
        $extra = $gitBranch ? "git branch " . TextRenderer::color($gitBranch, TextRenderer::COLOR_YELLOW) : '';

        return TextRenderer::color('MMLC cli', TextRenderer::COLOR_GREEN)
            . ' version ' . TextRenderer::color($mmlcVersion, TextRenderer::COLOR_YELLOW)
            . ' ' . $extra . "\n";
    }

    private function getCurrentGitBranch(string $gitPath): ?string
    {
        if (!is_dir($gitPath)) {
            return null;
        }

        $os = strtoupper(substr(PHP_OS, 0, 3));
        $command = '';

        switch ($os) {
            case 'WIN':
                $command = 'cd /d "' . $gitPath . '" & git symbolic-ref --short HEAD 2>NUL';
                break;
            case 'LIN':
            case 'DAR':
                $command = 'cd "' . $gitPath . '" && git symbolic-ref --short HEAD 2>/dev/null';
                break;
            default:
                return 'unkown branch';
        }

        $output = trim('' . shell_exec($command));

        if (empty($output)) {
            return null;
        }

        return $output;
    }
}
