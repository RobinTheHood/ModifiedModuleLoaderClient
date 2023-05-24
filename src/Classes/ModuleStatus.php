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

namespace RobinTheHood\ModifiedModuleLoaderClient;

use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\ConstraintParser;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\ParseErrorException;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;

class ModuleStatus
{
    public static function isValid(Module $module): bool
    {
        return !($module->isRemote() && $module->isLoaded());
    }

    public static function isLoadable(Module $module): bool
    {
        return
            ($module->isLoadable() && $module->isRemote()) ||
            !$module->isRemote();
    }

    public static function isCompatibleLoadable(Module $module): bool
    {
        return
            $module->isLoadable() &&
            $module->isRemote() &&
            !$module->isLoaded() &&
            !$module->isInstalled() &&
            $module->isCompatible();
    }

    public static function isIncompatibleLoadebale(Module $module): bool
    {
        return
            $module->isLoadable() &&
            $module->isRemote() &&
            !$module->isLoaded() &&
            !$module->isInstalled() &&
            !$module->isCompatible();
    }

    public static function isRepairable(Module $module): bool
    {
        return
            !$module->isRemote() &&
            $module->isLoaded() &&
            $module->isInstalled() &&
            $module->isChanged();
    }

    public static function isUninstallable(Module $module): bool
    {
        return
            !$module->isRemote() &&
            $module->isLoaded() &&
            $module->isInstalled();
    }

    public static function isInstallable(Module $module): bool
    {
        return
            !$module->isRemote() &&
            $module->isLoaded() &&
            !$module->getInstalledVersion();
    }

    public static function isCompatibleInstallable(Module $module): bool
    {
        return
            !$module->isRemote() &&
            $module->isLoaded() &&
            !$module->getInstalledVersion() &&
            $module->isCompatible();
    }

    public static function isCompatibleLoadableAndInstallable(Module $module): bool
    {
        return
            $module->isLoadable() &&
            $module->isRemote() &&
            $module->isCompatible() &&

            !$module->isLoaded() &&
            !$module->isInstalled() &&
            !$module->getInstalledVersion();
    }

    public static function isIncompatibleInstallable(Module $module): bool
    {
        return
            !$module->isRemote() &&
            $module->isLoaded() &&
            !$module->getInstalledVersion() &&
            !$module->isCompatible();
    }

    public static function isCompatible(Module $module): bool
    {
        return $module->isCompatible();
    }

    public static function isIncompatible(Module $module): bool
    {
        return !$module->isCompatible();
    }

    public static function isUpdatable(Module $module): bool
    {
        if ($module->isRemote()) {
            return false;
        }

        if (!$module->isInstalled()) {
            return false;
        }

        $installedVersion = $module->getInstalledVersion();
        $newestVersion = $module->getNewestVersion();

        if (!$installedVersion) {
            return false;
        }

        if (!$newestVersion->isLoadable()) {
            return false;
        }

        $comparator = new Comparator(new Parser());
        if (!$comparator->greaterThan($newestVersion->getVersion(), $installedVersion->getVersion())) {
            return false;
        }

        return $module->isInstalled();
    }

    public static function hasValidRequire(Module $module): string
    {
        $constraintParser = new ConstraintParser(new Parser());
        foreach ($module->getRequire() as $module => $constraintString) {
            try {
                $constraintParser->parse($constraintString);
            } catch (ParseErrorException $e) {
                return $e->getMessage();
            }
        }
        return '';
    }
}
