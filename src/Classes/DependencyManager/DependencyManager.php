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

namespace RobinTheHood\ModifiedModuleLoaderClient\DependencyManager;

use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\Combination;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\CombinationSatisfyerResult;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\DependencyBuilder;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\SystemSetFactory;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\ModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Logger\LogLevel;
use RobinTheHood\ModifiedModuleLoaderClient\Logger\StaticLogger;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;
use RobinTheHood\ModifiedModuleLoaderClient\SemverComparatorFactory;

class DependencyManager
{
    /** @var Comparator */
    protected $comparator;

    public function __construct()
    {
        $this->comparator = SemverComparatorFactory::createComparator();
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

    /**
     * @param Combination $combination
     *
     * @return Module[]
     */
    public function getAllModulesFromCombination(Combination $combination): array
    {
        $moduleLoader = ModuleLoader::getModuleLoader();
        $moduleLoader->resetCache();

        $modules = [];
        foreach ($combination->strip()->getAll() as $archiveName => $version) {
            $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);
            if (!$module) {
                $message = "Can not find Module {$archiveName} in version {$version}";
                StaticLogger::log(LogLevel::WARNING, $message);
                throw new DependencyException($message);
            }
            $modules[] = $module;
        }
        return $modules;
    }

    /**
     * @param Module $module
     * @param string[] $doNotCheck
     *
     * @return CombinationSatisfyerResult
     */
    public function canBeInstalled(Module $module, array $doNotCheck = []): CombinationSatisfyerResult
    {
        $systemSetFactory = new SystemSetFactory();
        $systemSet = $systemSetFactory->getSystemSet();

        foreach ($doNotCheck as $name) {
            $systemSet->remove($name);
        }

        $dependencyBuilder = new DependencyBuilder();
        $combinationSatisfyerResult = $dependencyBuilder->satisfies(
            $module->getArchiveName(),
            $module->getVersion(),
            $systemSet
        );

        if ($combinationSatisfyerResult->result === CombinationSatisfyerResult::RESULT_COMBINATION_NOT_FOUND) {
            $message = "Can not install module {$module->getArchiveName()} in version {$module->getVersion()} "
            . "because there are conflicting version contraints. "
            . "Perhaps you have installed a module that requires a different version, "
            . "or there is no compatible combination of dependencies. "
            . " The following combination is required: {$combinationSatisfyerResult->failLog}";

            StaticLogger::log(LogLevel::WARNING, $message);
            throw new DependencyException($message);
        }

        // $modules = $this->getAllModulesFromCombination($combinationSatisfyerResult->foundCombination);
        // $this->canBeInstalledTestChanged($module, $modules);

        return $combinationSatisfyerResult;
    }

    /**
     * Testet, ob das Modul in $module installiert werden kann, ob das Modul $module
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
     * Liefert alle fehlenden (nicht installierte) Abhängigkeiten zu einem Modul
     *
     * @return array<string, string>
     */
    public function getMissingDependencies(Module $module): array
    {
        $moduleLoader = LocalModuleLoader::getModuleLoader();
        $moduleLoader->resetCache();
        $missing = [];

        foreach ($module->getRequire() as $archiveName => $version) {
            $found = false;
            $depModules = $moduleLoader->loadAllVersionsByArchiveName($archiveName);
            foreach ($depModules as $depModule) {
                if (!$depModule->isInstalled()) {
                    continue;
                }

                if (!$this->comparator->satisfies($depModule->getVersion(), $version)) {
                    continue;
                }

                $found = true;
                $missing += $this->getMissingDependencies($depModule);
                break;
            }

            if (!$found) {
                $missing += [$archiveName => $version];
            }
        }

        return $missing;
    }
}
