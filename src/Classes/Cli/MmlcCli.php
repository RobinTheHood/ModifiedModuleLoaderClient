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

class MmlcCli
{
    public function __construct()
    {
        //TODO: Initialize the application here ...
    }

    public function run()
    {
        $command = $this->getCommand();

        if ($this->hasOption('--help')) {
            $this->showCommandHelp($command);
        } else {
            switch ($command) {
                case 'download':
                    $archiveName = $this->getArgument(2);
                    $this->downloadModule($archiveName);
                    break;
                case 'install':
                    $archiveName = $this->getArgument(2);
                    $force = $this->hasOption('-f') || $this->hasOption('--force');
                    $this->installModule($archiveName, $force);
                    break;
                case 'update':
                    $archiveName = $this->getArgument(2);
                    $force = $this->hasOption('-f') || $this->hasOption('--force');
                    $this->updateModule($archiveName, $force);
                    break;
                case 'uninstall':
                    $archiveName = $this->getArgument(2);
                    $force = $this->hasOption('-f') || $this->hasOption('--force');
                    $this->uninstallModule($archiveName, $force);
                    break;
                case 'list':
                    $this->listModules();
                    break;
                case 'search':
                    $searchTerm = $this->getArgument(2);
                    $this->searchModules($searchTerm);
                    break;
                case 'info':
                    $archiveName = $this->getArgument(2);
                    $this->moduleInfo($archiveName);
                    break;
                case 'status':
                    $this->moduleStatus();
                    break;
                case 'create':
                    if ($this->hasOption('-i')) {
                        $this->createModuleInteractive();
                    } else {
                        $this->createModule();
                    }
                    break;
                case 'listen':
                    $this->listenForFileChanges();
                    break;
                case 'discard':
                    $archiveName = $this->getArgument(2);
                    $force = $this->hasOption('-f') || $this->hasOption('--force');
                    $this->discardChanges($archiveName, $force);
                    break;
                case 'self-update':
                    $this->selfUpdate();
                    break;
                default:
                    $this->showHelp();
                    break;
            }
        }
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

    private function showHelp()
    {
        $this->shopHelpLogo();
        echo "\n";
        echo "\e[32mMMLC cli\e[0m version \e[33m1.21.0\e[0m\n";
        echo "\n";
        echo "Usage: mmlc <command> [options]\n";
        echo "\n";
        echo "Commands:\n";
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
        echo "  \e[32mlisten\e[0m                  Automatically detect and apply file changes for module\n";
        echo "                          development.\n";
        echo "  \e[32mdiscard\e[0m <archiveName>   Discard changes to a module. Use the -f or --force\n";
        echo "                          option to enforce.\n";
        echo "  \e[32mself-update\e[0m             Updates MMLC to the latest version.\n";
        echo "\n";
        echo "Options:\n";
        echo "  -h, --help     Show this help.\n";
        echo "  -v, --version  Show the MMLC version.\n";
    }


    private function showCommandHelp($command)
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
            case 'listen':
                echo "Usage: mmlc listen                  Automatically detect and apply file changes for module development.\n";
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
        echo "\n";
        echo "Options:\n";
        echo "  -h, --help     Show this help.\n";
    }


    // Placeholder functions, replace with your actual implementation for each command

    private function downloadModule($archiveName)
    {
        // Implement module download logic
    }

    private function installModule($archiveName, $force = false)
    {
        // Implement module installation logic
    }

    private function updateModule($archiveName, $force = false)
    {
        // Implement module update logic
    }

    private function uninstallModule($archiveName, $force = false)
    {
        // Implement module uninstallation logic
    }

    private function listModules()
    {
        // Implement listing available modules
    }

    private function searchModules($searchTerm)
    {
        // Implement module search logic
    }

    private function moduleInfo($archiveName)
    {
        // Implement displaying module information
    }

    private function moduleStatus()
    {
        // Implement displaying module status
    }

    private function createModule()
    {
        // Implement module creation logic
    }

    private function createModuleInteractive()
    {
        // Implement interactive module creation logic
    }

    private function listenForFileChanges()
    {
        // Implement file change detection logic
    }

    private function discardChanges($archiveName, $force = false)
    {
        // Implement discard changes logic
    }

    private function selfUpdate()
    {
        // Implement self-update logic
    }

    private function getCommand()
    {
        global $argv;
        return isset($argv[1]) ? $argv[1] : 'help';
    }

    private function getArgument($index)
    {
        global $argv;
        return isset($argv[$index]) ? $argv[$index] : null;
    }

    private function hasOption($option)
    {
        global $argv;
        return in_array($option, $argv);
    }
}
