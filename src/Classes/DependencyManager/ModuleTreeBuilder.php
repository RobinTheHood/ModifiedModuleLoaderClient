<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace RobinTheHood\ModifiedModuleLoaderClient\DependencyManager;

use RobinTheHood\ModifiedModuleLoaderClient\Loader\ModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFilter;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleSorter;

class ModuleTreeBuilder
{
    /** @var Module[] */
    private $moduleCache = [];

    /**
     * @param string $archiveName
     * @param string $versionConstraint
     * @return Module[]
     */
    private function loadAllByArchiveNameAndConstraint(string $archiveName, string $versionConstraint): array
    {
        $modules = $this->moduleCache[$archiveName] ?? [];
        if (!$modules) {
            $moduleLoader = ModuleLoader::getModuleLoader();
            $modules = $moduleLoader->loadAllVersionsByArchiveName($archiveName);
            $modules = ModuleSorter::sortByVersion($modules);
            $this->moduleCache[$archiveName] = $modules;
        }

        return ModuleFilter::filterByVersionConstrain($modules, $versionConstraint);
    }

    /**
     * @param Module $Module
     * @param int $depth
     * @return ModuleTree[]
     */
    public function buildListByConstraints(Module $module, int $depth = 0): array
    {
        if ($depth >= 10) {
            return [];
        }

        $require = $module->getRequire();

        $moduleTrees = [];
        foreach ($require as $archiveName => $versionConstraint) {
            // Modules to Entry
            $moduleTree = new ModuleTree();
            $moduleTree->archiveName = $archiveName;
            $moduleTree->versionConstraint = $versionConstraint;

            // Fetch Versions
            $modules = $this->loadAllByArchiveNameAndConstraint($archiveName, $versionConstraint);

            // VersionList
            foreach ($modules as $module) {
                $moduleVersion = new ModuleVersion();
                $moduleVersion->version = $module->getVersion();
                $moduleVersion->require = $this->buildListByConstraints($module, $depth + 1);
                $moduleTree->moduleVersions[$moduleVersion->version] = $moduleVersion;
            }

            $moduleTrees[] = $moduleTree;
        }

        return $moduleTrees;
    }

    /**
     * @param string $archiveName
     * @param string $versionConstraint
     * @param int $depth
     */
    public function buildByConstraints(string $archiveName, string $versionConstraint, int $depth = 0): ModuleTree
    {
        $moduleTree = new ModuleTree();
        $moduleTree->archiveName = $archiveName;
        $moduleTree->versionConstraint = $versionConstraint;

        $modules = $this->loadAllByArchiveNameAndConstraint($archiveName, $versionConstraint);

        $moduleVersions = [];
        foreach ($modules as $module) {
            // Context: Module
            $moduleVersion = new ModuleVersion();
            $moduleVersion->version = $module->getVersion();

            if ($depth < 10) {
                $require = $module->getRequire();
                foreach ($require as $archiveName => $versionConstraint) {
                    // Context: require
                    $moduleVersion->require[] = $this->buildByConstraints($archiveName, $versionConstraint, $depth + 1);
                }
            }
            $moduleVersions[] = $moduleVersion;
        }
        $moduleTree->moduleVersions = $moduleVersions;

        return $moduleTree;
    }
}
