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

namespace RobinTheHood\ModifiedModuleLoaderClient\ModuleManager;

interface ModuleManagerLoggerInterface
{
    public function debug(ModuleManagerMessage $message): void;
    public function info(ModuleManagerMessage $message): void;
    public function notice(ModuleManagerMessage $message): void;
    public function warning(ModuleManagerMessage $message): void;
    public function error(ModuleManagerMessage $message): void;
}
