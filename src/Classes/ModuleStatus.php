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

    public static function isCompatibleLoadebale(Module $module): bool
    {
        return
            $module->isLoadable() &&
            $module->isRemote() &&
            !$module->isLoaded() &&
            !$module->isInstalled() &&
            $module->isCompatible();
    }

    public static function isUncompatibleLoadebale(Module $module): bool
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

    public static function isUncompatibleInstallable(Module $module): bool
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

    public static function isUncompatible(Module $module): bool
    {
        return !$module->isCompatible();
    }

    public static function isUpdatable(Module $module): bool
    {
        $installedVersion = $module->getInstalledVersion();
        $newestVersion = $module->getNewestVersion();

        if (!$newestVersion || !$installedVersion) {
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
}
