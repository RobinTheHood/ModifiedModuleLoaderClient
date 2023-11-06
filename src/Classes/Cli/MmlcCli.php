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
use RobinTheHood\ModifiedModuleLoaderClient\Cli\Command\CommandDownload;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\Command\CommandInfo;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\Command\CommandList;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\Command\CommandWatch;

class MmlcCli extends Cli
{
    public function __construct()
    {
        $this->addCommand(new CommandDownload());
        $this->addCommand(new CommandList());
        $this->addCommand(new CommandInfo());
        $this->addCommand(new CommandWatch());
    }

    public function run()
    {
        $command = $this->getCommand();

        if (!$command && ($this->hasOption('--version') || $this->hasOption('-v'))) {
            $this->showVersion();
        } elseif ($command && ($this->hasOption('--help') || $this->hasOption('-h'))) {
            $command->runHelp($this);
            return;
        } elseif ($command) {
            $command->run($this);
            return;
        } else {
            $this->runHelp();
        }
    }

    private function runHelp()
    {
        TextRenderer::renderLogo();
        echo "\n";

        $this->showVersion();
        echo "\n";

        TextRenderer::renderHelpHeading('Usage:');
        echo "  command [options]\n";
        echo "\n";

        TextRenderer::renderHelpHeading('Options:');
        TextRenderer::renderHelpOption('h', 'help', 'Display help for the given command.');
        TextRenderer::renderHelpOption('v', 'version', 'Display this application version.');
        echo "\n";

        TextRenderer::renderHelpHeading('Commands:');
        TextRenderer::renderHelpCommand('download', 'Download the latest version of module.');
        TextRenderer::renderHelpCommand('install', 'Download and install a module in your shop. Use the -f or --force option to enforce.');
        TextRenderer::renderHelpCommand('update', 'Update an already installed module to the latest version. Use the -f or --force option to enforce.');
        TextRenderer::renderHelpCommand('uninstall', 'Uninstall a module from your shop. Use the -f or --force option to enforce.');
        TextRenderer::renderHelpCommand('list', 'List all available modules that can be used with MMLC.');
        TextRenderer::renderHelpCommand('search', 'Search for modules based on a specific search term.');
        TextRenderer::renderHelpCommand('info', 'Display information and details for a specific module.');
        TextRenderer::renderHelpCommand('status', 'Show the status of all installed modules in MMLC.');
        TextRenderer::renderHelpCommand('create', 'Create a new module in MMLC. Use the -i option for the interactive mode.');
        TextRenderer::renderHelpCommand('watch', 'Automatically detect and apply file changes for module development.');
        TextRenderer::renderHelpCommand('discard', 'Discard changes to a module. Use the -f or --force option to enforce.');
        TextRenderer::renderHelpCommand('self-update', 'Updates MMLC to the latest version.');
        echo "\n";

        echo "Read more at https://module-loader.de/documentation.php\n";
    }

    private function showVersion()
    {
        $mmlcVersion = App::getMmlcVersion();
        $gitBranch = $this->getCurrentGitBranch(App::getRoot() . '/.git');
        $extra = $gitBranch ? "git branch " . TextRenderer::color($gitBranch, TextRenderer::COLOR_YELLOW) : '';

        echo TextRenderer::color('MMLC cli', TextRenderer::COLOR_GREEN)
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
