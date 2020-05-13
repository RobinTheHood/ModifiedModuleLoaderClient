<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient;

use RobinTheHood\ModifiedModuleLoaderClient\Semver;

class ModuleStatus
{
    public static function isValid($module)
    {
        return !($module->isRemote() && $module->isLoaded());
    }

    public static function isLoadable($module)
    {
        return
            ($module->isLoadable() && $module->isRemote()) ||
            !$module->isRemote();
    }

    public static function isCompatibleLoadebale($module)
    {
        return
            $module->isLoadable() &&
            $module->isRemote() &&
            !$module->isLoaded() &&
            !$module->isInstalled() &&
            $module->isCompatible();
    }

    public static function isUncompatibleLoadebale($module)
    {
        return
            $module->isLoadable() &&
            $module->isRemote() &&
            !$module->isLoaded() &&
            !$module->isInstalled() &&
            !$module->isCompatible();
    }

    public static function isRepairable($module)
    {
        return
            !$module->isRemote() &&
            $module->isLoaded() &&
            $module->isInstalled() &&
            $module->isChanged();
    }

    public static function isUninstallable($module)
    {
        return
            !$module->isRemote() &&
            $module->isLoaded() &&
            $module->isInstalled();
    }

    public static function isInstallable($module)
    {
        return
            !$module->isRemote() &&
            $module->isLoaded() &&
            !$module->getInstalledVersion();
    }

    public static function isCompatibleInstallable($module)
    {
        return
            !$module->isRemote() &&
            $module->isLoaded() &&
            !$module->getInstalledVersion() &&
            $module->isCompatible();
    }

    public static function isUncompatibleInstallable($module)
    {
        return
            !$module->isRemote() &&
            $module->isLoaded() &&
            !$module->getInstalledVersion() &&
            !$module->isCompatible();
    }

    public static function isCompatible($module)
    {
        return $module->isCompatible();
    }

    public static function isUncompatible($module)
    {
        return !$module->isCompatible();
    }

    public static function isUpdateable($module)
    {
        $installedVersion = $module->getInstalledVersion();
        $newestVersion = $module->getNewestVersion();

        if (!$newestVersion || !$installedVersion) {
            return false;
        }

        if (!$newestVersion->isLoadable()) {
            return false;
        }

        if (!Semver::greaterThan($newestVersion->getVersion(), $installedVersion->getVersion())) {
            return false;
        }

        return $module->isInstalled();
    }
}
