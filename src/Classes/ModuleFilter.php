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

use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleStatus;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;

class ModuleFilter
{
    /** @var Comparator */
    public $comparator;

    public static function create(int $mode): ModuleFilter
    {
        $comparator = Comparator::create($mode);
        $moduleFilter = new ModuleFilter($comparator);
        return $moduleFilter;
    }

    public static function createFromConfig(): ModuleFilter
    {
        return self::create(Config::getDependenyMode());
    }

    public function __construct(Comparator $comparator)
    {
        $this->comparator = $comparator;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public function filterLoaded(array $modules): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            if ($module->isLoaded()) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public function filterInstalled(array $modules): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            if ($module->isInstalled()) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public function filterUpdatable(array $modules): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            if (ModuleStatus::isUpdatable($module)) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public function filterRepairable(array $modules): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            if (ModuleStatus::isRepairable($module)) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public function filterNotLoaded(array $modules): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            if (!$module->isLoaded()) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public function filterValid(array $modules): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            if (ModuleStatus::isValid($module)) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public function filterNewestVersion(array $modules): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            $insertOrReplace = true;
            foreach ($filteredModules as $filteredModule) {
                if ($module->getArchiveName() != $filteredModule->getArchiveName()) {
                    continue;
                }

                if ($this->comparator->lessThan($module->getVersion(), $filteredModule->getVersion())) {
                    $insertOrReplace = false;
                    break;
                }
            }

            if ($insertOrReplace) {
                $filteredModules[$module->getArchiveName()] = $module;
            }
        }

        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public function filterNewestOrInstalledVersion($modules): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            $insertOrReplace = true;
            foreach ($filteredModules as $filteredModule) {
                if ($module->getArchiveName() != $filteredModule->getArchiveName()) {
                    continue;
                }

                if ($filteredModule->isInstalled()) {
                    $insertOrReplace = false;
                    break;
                }

                if ($module->isInstalled()) {
                    break;
                }

                if ($this->comparator->lessThan($module->getVersion(), $filteredModule->getVersion())) {
                    $insertOrReplace = false;
                    break;
                }
            }

            if ($insertOrReplace) {
                $filteredModules[$module->getArchiveName()] = $module;
            }
        }

        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public function filterByArchiveName(array $modules, string $archiveName): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            if ($module->getArchiveName() == $archiveName) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public function filterByVersion(array $modules, string $version): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            if ($module->getVersion() == $version) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    public function filterByVersionConstrain(array $modules, string $constrain): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            if ($this->comparator->satisfies($module->getVersion(), $constrain)) {
                $filteredModules[] = $module;
            }
        }
        return $filteredModules;
    }

    /**
     * @param Module[] $modules
     */
    public function getLatestVersion(array $modules): ?Module
    {
        $selectedModule = null;
        foreach ($modules as $module) {
            if (
                !$selectedModule
                || $this->comparator->greaterThan($module->getVersion(), $selectedModule->getVersion())
            ) {
                $selectedModule = $module;
            }
        }
        return $selectedModule;
    }

    /**
     * @param Module[] $modules
     * @return Module|null
     */
    public function getByArchiveNameAndVersion(array $modules, string $archiveName, string $version): ?Module
    {
        foreach ($modules as $module) {
            if ($module->getArchiveName() != $archiveName) {
                continue;
            }

            if ($module->getVersion() == $version) {
                return $module;
            }
        }
        return null;
    }
}
