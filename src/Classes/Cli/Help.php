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

class Help
{
    public function addCommand(string $name, string $description): void
    {
    }

    private function rightPad($text, $totalLength)
    {
        $textLength = strlen($text);

        if ($textLength >= $totalLength) {
            return $text; // Der Text ist bereits länger oder gleich der Ziel-Länge
        }

        $paddingLength = $totalLength - $textLength;
        $padding = str_repeat(' ', $paddingLength);

        return $text . $padding;
    }

    public function renderHeading(string $heading): void
    {
        echo "\e[33m$heading\e[0m\n";
    }

    public function renderCommand($name, $description)
    {
        $name = $this->rightPad($name, 20);
        echo "  \e[32m$name\e[0m $description\n";
    }

    private function shopHelpLogo()
    {
        // echo "    __  _____  _____    ______   ________    ____\n";
        // echo "   /  |/  /  |/  / /   / ____/  / ____/ /   /  _/\n";
        // echo "  / /|_/ / /|_/ / /   / /      / /   / /    / /  \n";
        // echo " / /  / / /  / / /___/ /___   / /___/ /____/ /   \n";
        // echo "/_/  /_/_/  /_/_____/\____/   \____/_____/___/   \n";
        // created with: https://patorjk.com/software/taag/#p=display&f=Slant&t=MMLC%20CLI

        echo "    __  ___ __  ___ __    ______   ______ __     ____\n";
        echo "   /  |/  //  |/  // /   / ____/  / ____// /    /  _/\n";
        echo "  / /|_/ // /|_/ // /   / /      / /    / /     / /  \n";
        echo " / /  / // /  / // /___/ /___   / /___ / /___ _/ /   \n";
        echo "/_/  /_//_/  /_//_____/\____/   \____//_____//___/   \n";
        // cretated with: https://patorjk.com/software/taag/#p=display&h=1&f=Slant&t=MMLC%20CLI
    }

    public function showHelp()
    {
        $mmlcVersion = App::getMmlcVersion();
        $gitBranch = $this->getCurrentGitBranch(App::getRoot() . '/.git');
        $extra = $gitBranch ? "git branch \e[33m$gitBranch\e[0m" : '';

        $this->shopHelpLogo();
        echo "\n";
        echo "\e[32mMMLC cli\e[0m version \e[33m$mmlcVersion\e[0m $extra\n";
        echo "\n";
        $this->renderHeading('Usage:');
        echo "  command [options]\n";
        echo "\n";
        $this->renderHeading('Options:');
        echo "  -h, --help     Display help for the given command.\n";
        echo "  -v, --version  Display this application version.\n";
        echo "\n";
        $this->renderHeading('Commands:');
        $this->renderCommand('download', 'Download the latest version of module.');
        $this->renderCommand('install', 'Download and install a module in your shop. Use the -f or --force option to enforce.');
        $this->renderCommand('update', 'Update an already installed module to the latest version. Use the -f or --force option to enforce.');
        $this->renderCommand('uninstall', 'Uninstall a module from your shop. Use the -f or --force option to enforce.');
        $this->renderCommand('list', 'List all available modules that can be used with MMLC.');
        $this->renderCommand('search', 'Search for modules based on a specific search term.');
        $this->renderCommand('info', 'Display information and details for a specific module.');
        $this->renderCommand('status', 'Show the status of all installed modules in MMLC.');
        $this->renderCommand('create', 'Create a new module in MMLC. Use the -i option for the interactive mode.');
        $this->renderCommand('watch', 'Automatically detect and apply file changes for module development.');
        $this->renderCommand('discard', 'Discard changes to a module. Use the -f or --force option to enforce.');
        $this->renderCommand('self-update', 'Updates MMLC to the latest version.');
    }


    public function showCommandHelp($command)
    {
        echo "MMLC - Modified Module Loader Client - cli\n";
        echo "\n";
        switch ($command) {
            case 'download':
                echo "Usage: mmlc download <archiveName>  Download the latest version of a module.\n";
                break;
            case 'install':
                echo "Usage: mmlc install <archiveName>   Download and install a module in your shop.\n";
                echo "                                    Use the -f or --force option to enforce.\n";
                break;
            case 'update':
                echo "Usage: mmlc update <archiveName>    Update an already installed module to the latest version.\n";
                echo "                                    Use the -f or --force option to enforce.\n";
                break;
            case 'uninstall':
                echo "Usage: mmlc uninstall <archiveName> Uninstall a module from your shop. Use the -f or --force option to enforce.\n";
                break;
            case 'list':
                echo "Usage: mmlc list                    List all available modules that can be used with MMLC.\n";
                break;
            case 'search':
                echo "Usage: mmlc search <searchTerm>     Search for modules based on a specific search term.\n";
                break;
            case 'info':
                echo "Usage: mmlc info <archiveName>      Display information and details for a specific module.\n";
                break;
            case 'status':
                echo "Usage: mmlc status                  Show the status of all installed modules in MMLC.\n";
                break;
            case 'create':
                echo "Usage: mmlc create                  Create a new module in MMLC.\n";
                echo "Options:\n";
                echo "  -i, --interactive  Start the interactive mode for module creation, where MMLC will ask questions that you need to answer.\n";
                break;
            case 'watch':
                echo "Usage: mmlc watch                  Automatically detect and apply file changes for module development.\n";
                break;
            case 'discard':
                echo "Usage: mmlc discard <archiveName>   Discard changes to a module. Use the -f or --force option to enforce.\n";
                break;
            case 'self-update':
                echo "Usage: mmlc self-update             Updates MMLC to the latest version.\n";
                break;
            default:
                $this->showHelp();
                break;
        }
    }

    private function showHelpOld()
    {
        $this->shopHelpLogo();
        echo "\n";
        echo "\e[32mMMLC cli\e[0m version \e[33m1.21.0\e[0m\n";
        echo "\n";
        echo "Usage: mmlc <command> [options]\n";
        echo "\n";
        echo "Commands:\n";
        $this->renderCommand('download', 'Download the latest version of module.');
        echo "  \e[32mdownload\e[0m <archiveName>  Download the latest version of module.\n";
        echo "  \e[32minstall\e[0m <archiveName>   Download and install a module in your shop.\n";
        echo "                          Use the -f or --force option to enforce.\n";
        echo "  \e[32mupdate\e[0m <archiveName>    Update an already installed module to the latest version.\n";
        echo "                          Use the -f or --force option to enforce.\n";
        echo "  \e[32muninstall\e[0m <archiveName> Uninstall a module from your shop. Use the -f or --force\n";
        echo "                          option to enforce.\n";
        echo "  \e[32mlist\e[0m                    List all available modules that can be used with MMLC.\n";
        echo "  \e[32msearch\e[0m <searchTerm>     Search for modules based on a specific search term.\n";
        echo "  \e[32minfo\e[0m <archiveName>      Display information and details for a specific module.\n";
        echo "  \e[32mstatus\e[0m                  Show the status of all installed modules in MMLC.\n";
        echo "  \e[32mcreate\e[0m                  Create a new module in MMLC. Use the -i option for the\n";
        echo "                          interactive mode.\n";
        echo "  \e[32mwatch\e[0m                  Automatically detect and apply file changes for module\n";
        echo "                          development.\n";
        echo "  \e[32mdiscard\e[0m <archiveName>   Discard changes to a module. Use the -f or --force\n";
        echo "                          option to enforce.\n";
        echo "  \e[32mself-update\e[0m             Updates MMLC to the latest version.\n";
        echo "\n";
        echo "Options:\n";
        echo "  -h, --help     Show this help.\n";
        echo "  -v, --version  Show the MMLC version.\n";
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
