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

use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\CombinationSatisfyerResult;
use RobinTheHood\ModifiedModuleLoaderClient\Module;

class ModuleManagerMessage
{
    public const PULL_INFO_START = 100;
    public const PULL_ERROR_MODULE_NOT_FOUND = 151;
    public const PULL_ERROR_MODULE_ALLREADY_LOADED = 152;

    public const DELETE_INFO_START = 200;
    public const DELETE_ERROR_MODULE_NOT_FOUND = 251;
    public const DELETE_ERROR_MODULE_IS_INSTALED = 252;

    public const INSTALL_INFO_START = 300;
    public const INSTALL_INFO_PULL_MODULE_START = 301;
    public const INSTALL_INFO_UPDATE_AUTOLOAD_START = 302;
    public const INSTALL_ERROR_MODULE_NOT_FOUND = 351;
    public const INSTALL_ERROR_MODULE_MISSING_REQUIREMENTS = 352;
    public const INSTALL_ERROR_MODULE_ALLREADY_INSTALED = 353;

    public const UPDATE_INFO_START = 400;
    public const UDPATE_INFO_PULL_MODULE_START = 401;
    public const UPDATE_INFO_UPDATE_AUTOLOAD_START = 402;
    public const UPDATE_INFO_TO = 403;
    public const UPDATE_ERROR_MODULE_NOT_FOUND = 451;
    public const UPDATE_ERROR_MODULE_NOT_INSTALLED = 452;
    public const UPDATE_ERROR_MODULE_MISSING_REQUIREMENTS = 453;
    public const UPDATE_ERROR_MODULE_IS_CHANGED = 454;

    public const DISCARD_INFO_START = 500;
    public const DISCARD_ERROR_MODULE_NOT_FOUND = 551;
    public const DISCARD_ERROR_MODULE_NOT_CHANGED = 552;

    public const UNINSTALL_INFO_START = 600;
    public const UNINSTALL_INFO_UPDATE_AUTOLOAD_START = 601;
    public const UNINSTALL_ERROR_MODULE_NOT_FOUND = 651;
    public const UNINSTALL_ERROR_MODULE_NOT_INSTALLED = 652;
    public const UNINSTALL_ERROR_MODULE_IS_CHANGED = 653;
    public const UNINSTALL_ERROR_MODULE_IS_USED_BY = 654;

    public const AUTOLOAD_ERROR_CAN_NOT_CREATE_AUTOLOAD_FILE = 701;

    /** @var int */
    private $code = 0;

    /** @var string */
    private $message = '';

    /** @var string */
    private $archiveName = '';

    /** @var string */
    private $version = '';

    /** @var string */
    private $versionContraint = '';

    /** @var Module */
    private $module;

    /** @var CombinationSatisfyerResult */
    private $combinationSatisfyerResult;

    public static function create(int $code): ModuleManagerMessage
    {
        return new ModuleManagerMessage($code);
    }

    public function __construct(int $code)
    {
        $this->code = $code;
    }

    public function setMessage(string $message): ModuleManagerMessage
    {
        $this->message = $message;
        return $this;
    }

    public function setArchiveName(string $archiveName): ModuleManagerMessage
    {
        $this->archiveName = $archiveName;
        return $this;
    }

    public function setVersion(string $version): ModuleManagerMessage
    {
        $this->version = $version;
        return $this;
    }

    public function setVersionConstraint(string $versionContraint): ModuleManagerMessage
    {
        $this->versionContraint = $versionContraint;
        return $this;
    }

    public function setModule(Module $module): ModuleManagerMessage
    {
        $this->module = $module;
        return $this;
    }

    public function setCombinationSatisfyerResult(
        CombinationSatisfyerResult $combinationSatisfyerResult
    ): ModuleManagerMessage {
        $this->combinationSatisfyerResult = $combinationSatisfyerResult;
        return $this;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    private function getModulName(): string
    {
        if ($this->module) {
            $name = "{$this->module->getArchiveName()} version {$this->module->getVersion()}";
        } elseif ($this->archiveName && $this->version) {
            $name = "{$this->archiveName} version {$this->version}";
        } elseif ($this->archiveName && $this->versionContraint) {
            $name = "{$this->archiveName} version {$this->versionContraint}";
        } elseif ($this->archiveName) {
            $name = "{$this->archiveName}";
        } else {
            $name = "unknown";
        }

        return "module $name";
    }

    private function getUsedBy(): string
    {
        $subModulesArchiveNames = [];
        foreach ($this->module->getUsedBy() as $subModule) {
            $subModulesArchiveNames[] .= $subModule->getArchiveName();
        }
        $usedBy = implode("\n", $subModulesArchiveNames);
        return $usedBy;
    }

    public function __toString()
    {
        if ($this->code === self::PULL_INFO_START) {
            return sprintf("Pulling %s ...", $this->getModulName());
        } elseif ($this->code === self::PULL_ERROR_MODULE_NOT_FOUND) {
            return sprintf("Can not pull %s, because module not found.", $this->getModulName());
        } elseif ($this->code === self::PULL_ERROR_MODULE_ALLREADY_LOADED) {
            return sprintf("Can not pull %s, because module is already loaded.", $this->getModulName());
        } elseif ($this->code === self::DELETE_INFO_START) {
            return sprintf("Deleting %s ...", $this->getModulName());
        } elseif ($this->code === self::DELETE_ERROR_MODULE_NOT_FOUND) {
            return sprintf("Can not delete %s, because module not found.", $this->getModulName());
        } elseif ($this->code === self::DELETE_ERROR_MODULE_IS_INSTALED) {
            return sprintf("Can not delete %s, because it is installed.", $this->getModulName());
        } elseif ($this->code === self::INSTALL_INFO_START) {
            return sprintf("Installing %s ...", $this->getModulName());
        } elseif ($this->code === self::INSTALL_INFO_PULL_MODULE_START) {
            return sprintf("Pulling %s ...", $this->getModulName());
        } elseif ($this->code === self::INSTALL_INFO_UPDATE_AUTOLOAD_START) {
            return sprintf("Updating autotoload file ...");
        } elseif ($this->code === self::INSTALL_ERROR_MODULE_NOT_FOUND) {
            return sprintf("Can not install %s, because module not found.", $this->getModulName());
        } elseif ($this->code === self::INSTALL_ERROR_MODULE_MISSING_REQUIREMENTS) {
            return sprintf(
                "Can not install %s, because not all requirements are met.\n%s",
                $this->getModulName(),
                '' . $this->combinationSatisfyerResult->failLog
            );
        } elseif ($this->code === self::INSTALL_ERROR_MODULE_ALLREADY_INSTALED) {
            return sprintf("Can not install %s, because it is already installed.", $this->getModulName());
        } elseif ($this->code === self::UPDATE_INFO_START) {
            return sprintf("Updating %s ...", $this->getModulName());
        } elseif ($this->code === self::UDPATE_INFO_PULL_MODULE_START) {
            return sprintf("Pulling %s ...", $this->getModulName());
        } elseif ($this->code === self::UPDATE_INFO_TO) {
            return sprintf("Updated to %s.", $this->getModulName());
        } elseif ($this->code === self::UPDATE_INFO_UPDATE_AUTOLOAD_START) {
            return sprintf("Updating autotoload file ...", $this->getModulName());
        } elseif ($this->code === self::UPDATE_ERROR_MODULE_NOT_FOUND) {
            return sprintf("Can not update %s, because module not found.", $this->getModulName());
        } elseif ($this->code === self::UPDATE_ERROR_MODULE_NOT_INSTALLED) {
            return sprintf("Can not update %s, because module is not installed.", $this->getModulName());
        } elseif ($this->code === self::UPDATE_ERROR_MODULE_MISSING_REQUIREMENTS) {
            return sprintf(
                "Can not update %s, because not all requirements are met.\n%s",
                $this->getModulName(),
                '' . $this->combinationSatisfyerResult->failLog
            );
        } elseif ($this->code === self::UPDATE_ERROR_MODULE_IS_CHANGED) {
            return sprintf("Can not update %s, because module has changes.", $this->getModulName());
        } elseif ($this->code === self::DISCARD_INFO_START) {
            return sprintf("Discarding %s ...", $this->getModulName());
        } elseif ($this->code === self::DISCARD_ERROR_MODULE_NOT_FOUND) {
            return sprintf("Can not discard %s, because module not found.", $this->getModulName());
        } elseif ($this->code === self::DISCARD_ERROR_MODULE_NOT_CHANGED) {
            return sprintf("Can not discard %s, because module has no changes.", $this->getModulName());
        } elseif ($this->code === self::UNINSTALL_INFO_START) {
            return sprintf("Uninstalling %s ...", $this->getModulName());
        } elseif ($this->code === self::UNINSTALL_INFO_UPDATE_AUTOLOAD_START) {
            return sprintf("Updating autotoload file ...", $this->getModulName());
        } elseif ($this->code === self::UNINSTALL_ERROR_MODULE_NOT_FOUND) {
            return sprintf("Can not uninstall %s, because module not found.", $this->getModulName());
        } elseif ($this->code === self::UNINSTALL_ERROR_MODULE_NOT_INSTALLED) {
            return sprintf("Can not uninstall %s, because module is not installed.", $this->getModulName());
        } elseif ($this->code === self::UNINSTALL_ERROR_MODULE_IS_CHANGED) {
            return sprintf("Can not uninstall %s, because module has changes.", $this->getModulName());
        } elseif ($this->code === self::UNINSTALL_ERROR_MODULE_IS_USED_BY) {
            return sprintf(
                "Can not uninstall %s, because module is used by other modules.\n%s",
                $this->getModulName(),
                $this->getUsedBy()
            );
        } elseif ($this->code === self::AUTOLOAD_ERROR_CAN_NOT_CREATE_AUTOLOAD_FILE) {
            return sprintf(
                "Can not create autoload file. %s."
                . "You can create the autoload file by installing or uninstalling any module.",
                $this->getMessage()
            );
        }

        return "Unknown message";
    }
}
