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

class ModuleManagerResult
{
    public const TYPE_ERROR = 1;
    public const TYPE_SUCCESS = 2;

    /** @var int */
    private $type;

    /** @var ModuleManagerMessage */
    private $message;

    /** @var Module */
    private $module;

    public function __construct(int $type)
    {
        $this->type = $type;
    }

    public static function success(): ModuleManagerResult
    {
        $moduleManagerResult = new ModuleManagerResult(self::TYPE_SUCCESS);
        return $moduleManagerResult;
    }

    public static function error(ModuleManagerMessage $message): ModuleManagerResult
    {
        $moduleManagerResult = new ModuleManagerResult(self::TYPE_ERROR);
        $moduleManagerResult->setMessage($message);
        return $moduleManagerResult;
    }

    public function setModule(Module $module): ModuleManagerResult
    {
        $this->module = $module;
        return $this;
    }

    public function setMessage(ModuleManagerMessage $message): ModuleManagerResult
    {
        $this->message = $message;
        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function getMessage(): ?ModuleManagerMessage
    {
        return $this->message;
    }
}
