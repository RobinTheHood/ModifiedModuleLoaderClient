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

use RobinTheHood\ModifiedModuleLoaderClient\Module;

class ModuleManagerLog
{
    /** @var callable */
    private $writeFunction;

    /** @var callable */
    private $errorFunction;

    public function setWriteFunction(callable $writeFunction)
    {
        $this->writeFunction = $writeFunction;
    }

    public function setErrorFunction(callable $errorFunction)
    {
        $this->errorFunction = $errorFunction;
    }

    public function write(string $message, $data1 = null, $data2 = null): void
    {
        $function = $this->writeFunction;
        $function($message, $data1, $data2);
    }

    public function error(int $errorNo, string $message, $data1 = null, $data2 = null): void
    {
        $function = $this->errorFunction;
        $function($errorNo, $message, $data1, $data2);
    }
}
