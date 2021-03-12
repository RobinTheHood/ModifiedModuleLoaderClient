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
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\ModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;

class DependencyManager
{
    protected $comparator;

    public function __construct()
    {
        $this->comparator = new Comparator(new Parser());
    }

    /**
     * Liefert eine Mögichkeit von Modulen/Versionen von denen das Modul $module
     * abhängt. Zur Info. Es kann ganz viele Versions-Komkombintionen geben, von
     * denen ein Modul habhängig ist. Die Methode liefert nur eine Möglichkeit an
     * kombinationen.
     *
     * @return Module[]
     */
    public function getAllModules($module): array
    {
        $requireModulesTree = $this->buildTreeByModule($module);

        $requireModules = [];
        $this->flattenTree($requireModulesTree, $requireModules);

        $requireModules = $this->getUniqueRequireModules($requireModules);

        $modules = [];
        foreach ($requireModules as $requireModule) {
            $modules[] = $requireModule['module'];
        }

        return $modules;
    }

    public function canBeInstalled(Module $module): void
    {
        $modules = $this->getAllModules($module);
        $modules[] = $module;
        foreach ($modules as $module) {
            $this->canBeInstalledTestRequiers($module, $modules);
            $this->canBeInstalledTestSelected($module, $modules);
            $this->canBeInstalledTestInstalled($module);
            $this->canBeInstalledTestChanged($module, $modules);
        }
    }

    /**
     * Test ob das Modul in $module installiert werden kann, ob das Modul $module
     * selbst oder eine Abhängigkeit in $modules im Status 'changed' ist.
     *
     * @param Module[] $modules
     */
    private function canBeInstalledTestChanged(Module $module, array $modules): void
    {
        $module = $module->getInstalledVersion();
        if ($module && $module->isInstalled() && $module->isChanged()) {
            $a = $module->getArchiveName();
            throw new DependencyException("Module $a can not be installed because the Module has changes");
        }

        foreach ($modules as $module) {
            if ($module && $module->isInstalled() && $module->isChanged()) {
                $a = $module->getArchiveName();
                throw new DependencyException("Required Module $a can not be installed because the Module has changes");
            }
        }
    }

    /**
     * Test Installed
     *
     * Überprüft ob das Module in $module installiert werden kann, ober ob es ein
     * bereits installites Modul gibt, das von dem Modul in $module in einer anderern Version
     * abhängig ist.
     */
    public function canBeInstalledTestInstalled(Module $module): void
    {
        $localModuleLoader = LocalModuleLoader::getModuleLoader();
        $installedModules = $localModuleLoader->loadAllInstalledVersions();
        $this->canBeInstalledTestSelected($module, $installedModules);
    }

    /**
     * Test Selected
     *
     * Überprüft ob das Module in $module installiert werden kann, oder ob es ein Modul
     * in $selectedModules gibt, dass von dem Modul in $module in einer anderen Version abhängig ist.
     *
     * @param Module[] $selectedModules
     */
    public function canBeInstalledTestSelected(Module $module, array $selectedModules): void
    {
        $usedByEntrys = $this->getUsedByEntrys($module, $selectedModules);
        foreach ($usedByEntrys as $usedByEntry) {
            if (!$this->comparator->satisfies($module->getVersion(), $usedByEntry['requiredVersion'])) {
                $a = $module->getArchiveName();
                $av = $module->getVersion();
                $b = $usedByEntry['module']->getArchiveName();
                $bv = $usedByEntry['requiredVersion'];
                throw new DependencyException("Module $a version $av can not be installed because module $b requires version $bv");
            }
        }
    }

    /**
     * Test Requiers
     *
     * Diese Methode überprüft, ob das Module in $module unter einigen Voraussetzungen
     * installiert werden kann. Es wird verglichen, ob die Module/Versionen in $selectedModules
     * ausreichen, um die Abhängigkeiten zu erfüllen, die $module benötigt.
     *
     * @param Module[] $selectedModules
     */
    public function canBeInstalledTestRequiers(Module $module, array $selectedModules): void
    {
        foreach ($module->getRequire() as $archiveName => $version) {
            $moduleFound = false;
            foreach ($selectedModules as $selectedModule) {
                if ($selectedModule->getArchiveName() != $archiveName) {
                    continue;
                }

                $moduleFound = true;
                if (!$this->comparator->satisfies($selectedModule->getVersion(), $version)) {
                    $a = $selectedModule->getArchiveName();
                    $av = $module->getVersion();
                    throw new DependencyException("Module $a version $av can not be installed because module $archiveName version $version is required");
                }
            }

            if (!$moduleFound) {
                throw new DependencyException("Module $archiveName version $version can not be installed because module was not found.");
            }
        }
    }



    /**
     * Liefert eine Liste mit allen Modulen aus $selectedModules, die das Modul
     * $module verwenden.
     *
     * @param Module[] $selectedModules
    */
    public function getUsedByEntrys(Module $module, array $selectedModules): array
    {
        $usedByEntrys = [];
        foreach ($selectedModules as $selectedModule) {
            foreach ($selectedModule->getRequire() as $archiveName => $version) {
                if ($archiveName == $module->getArchiveName()) {
                    $usedByEntrys[] = [
                        'module' => $selectedModule,
                        'requiredVersion' => $version
                    ];
                }
            }
        }
        return $usedByEntrys;
    }

    public function flattenTree($moduleTree, &$modules = null)
    {
        if (!$moduleTree) {
            return;
        }

        foreach ($moduleTree as $entry) {
            $modules[] = [
                'module' => $entry['module'],
                'requestedVersion' => $entry['requestedVersion'],
                'selectedVersion' => $entry['selectedVersion']
            ];
            $this->flattenTree($entry['require'], $modules);
        }
    }

    public function getUniqueRequireModules($requireModules)
    {
        $uniqueModules = [];
        foreach ($requireModules as $requireModule) {
            $index = $requireModule['module']->getArchiveName() . ':' . $requireModule['selectedVersion'];
            $uniqueModules[$index] = [
                'module' => $requireModule['module'],
                'requestedVersion' => $requireModule['requestedVersion'],
                'selectedVersion' => $requireModule['selectedVersion']
            ];
        }

        return array_values($uniqueModules);
    }

    public function buildTreeByModule(Module $module)
    {
        $requireModulesTree = $this->buildTreeByModuleRecursive($module);
        return $requireModulesTree;
    }

    public function buildTreeByModuleRecursive(Module $module, int $depth = 0)
    {
        if ($depth >= 5) {
            return false;
        }

        $require = $module->getRequire();

        $requireModulesTree = [];
        foreach ($require as $archiveName => $versionConstraint) {
            $moduleLoader = ModuleLoader::getModuleLoader();
            $requireModule = $moduleLoader->loadLatestByArchiveNameAndConstraint($archiveName, $versionConstraint);

            if (!$requireModule) {
                continue;
            }

            $entry = [];
            $entry['module'] = $requireModule;
            $entry['requestedVersion'] = $versionConstraint;
            $entry['selectedVersion'] = $requireModule->getVersion();
            $entry['require'] = [];
            $requireModules = $this->buildTreeByModuleRecursive($requireModule, ++$depth);

            if ($requireModules) {
                $entry['require'] = $requireModules;
            }

            $requireModulesTree[] = $entry;
        }
        return $requireModulesTree;
    }
}
