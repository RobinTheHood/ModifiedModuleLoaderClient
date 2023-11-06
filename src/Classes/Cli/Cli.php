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

use RobinTheHood\ModifiedModuleLoaderClient\Cli\Command\CommandInterface;

class Cli
{
    /** @var CommandInterface[] */
    private $commands = [];

    protected function addCommand(CommandInterface $command): void
    {
        $this->commands[$command->getName()] = $command;
    }

    public function getCommand(): ?CommandInterface
    {
        $commandStr = $this->getCommandStr();
        $command = $this->commands[$commandStr] ?? null;
        return $command;
    }

    public function getCommandStr()
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