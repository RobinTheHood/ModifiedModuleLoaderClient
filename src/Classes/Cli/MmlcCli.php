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

use RobinTheHood\ModifiedModuleLoaderClient\Cli\Command\CommandCreate;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\Command\CommandInfo;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\Command\CommandList;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\Command\CommandWatch;

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
                    $this->createModule();
                    break;
                case 'watch':
                    $this->watchForFileChanges();
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
        $command = new CommandCreate();
        $command->run($this);
    }

    private function watchForFileChanges()
    {
        $command = new CommandWatch();
        $command->run();
    }

    private function discardChanges($archiveName, $force = false)
    {
        // Implement discard changes logic
    }

    private function selfUpdate()
    {
        // Implement self-update logic
    }

    public function getCommand()
    {
        global $argv;
        return isset($argv[1]) ? $argv[1] : 'help';
    }

    public function getArgument($index)
    {
        global $argv;
        return isset($argv[$index]) ? $argv[$index] : null;
    }

    public function getFilteredArgument(int $argumentIndex): string
    {
        global $argv;

        $arguments = $argv;
        $filteredArguments = array();

        foreach ($arguments as $index => $argument) {
            if (0 === $index || 1 === $index) {
                continue;
            }

            if ('-' === \substr($argument, 0, 1)) {
                continue;
            }

            $filteredArguments[] = $argument;
        }

        return $filteredArguments[$argumentIndex] ?? '';
    }

    public function hasOption($option)
    {
        global $argv;
        return in_array($option, $argv);
    }
}
