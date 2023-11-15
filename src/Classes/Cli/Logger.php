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

use RobinTheHood\ModifiedModuleLoaderClient\ModuleManager\ModuleManagerLoggerInterface;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleManager\ModuleManagerMessage;

class Logger implements ModuleManagerLoggerInterface
{
    /** @var MmlcCli */
    private $cli;

    public function __construct(MmlcCli $cli)
    {
        $this->cli = $cli;
    }

    public function debug(ModuleManagerMessage $message): void
    {
    }

    public function info(ModuleManagerMessage $message): void
    {
        $this->cli->writeLine(
            TextRenderer::color("Info {$message->getCode()}: ", TextRenderer::COLOR_GREEN)
            . $message
        );
    }

    public function notice(ModuleManagerMessage $message): void
    {
    }

    public function warning(ModuleManagerMessage $message): void
    {
    }

    public function error(ModuleManagerMessage $message): void
    {
        $this->cli->writeLine(
            TextRenderer::color("Error {$message->getCode()}: ", TextRenderer::COLOR_RED)
            . $message
        );
    }
}
