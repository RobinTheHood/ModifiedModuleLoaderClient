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
            $help = new Help();
            $help->showCommandHelp($command);
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

    private function showHelp()
    {
        $help = new Help();
        $help->showHelp();
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
        $command = new CommandList();
        $command->run();
    }

    private function searchModules($searchTerm)
    {
        // Implement module search logic
    }

    private function moduleInfo($archiveName)
    {
        $command = new CommandInfo();
        $command->run($archiveName);
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
