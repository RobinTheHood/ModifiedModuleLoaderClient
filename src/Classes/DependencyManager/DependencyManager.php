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

use RobinTheHood\ModifiedModuleLoaderClient\Config;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\Combination;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\CombinationSatisfyerResult;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\DependencyBuilder;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\SystemSetFactory;
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\ModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Logger\LogLevel;
use RobinTheHood\ModifiedModuleLoaderClient\Logger\StaticLogger;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;

/**
 * The DependencyManager class provides functionality for managing and analyzing module dependencies within the
 * MMLC - ModifiedModuleLoaderClient.
 *
 * This class is responsible for handling and verifying the dependencies of modules, ensuring compatibility with the
 * current system configuration. It offers methods for identifying dependencies, determining whether a module can be
 * installed, retrieving missing dependencies, and more.
 */
class DependencyManager
{
    /** @var dependencyBuilder */
    private $dependencyBuilder;

    /** @var Comparator */
    private $comparator;

    /** @var ModuleLoader */
    private $moduleLoader;

    /** @var SystemSetFactory */
    private $systemSetFactory;

    public static function create(int $mode): DependencyManager
    {
        $dependencyBuilder = DependencyBuilder::create($mode);
        $comparator = Comparator::create($mode);
        $moduleLoader = ModuleLoader::create($mode);
        $systemSetFactory = SystemSetFactory::create($mode);
        $dependencyManager = new DependencyManager($dependencyBuilder, $comparator, $moduleLoader, $systemSetFactory);
        return $dependencyManager;
    }

    public static function createFromConfig(): DependencyManager
    {
        return self::create(Config::getDependenyMode());
    }

    public function __construct(
        DependencyBuilder $dependencyBuilder,
        Comparator $comparator,
        ModuleLoader $moduleLoader,
        SystemSetFactory $systemSetFactory
    ) {
        $this->dependencyBuilder = $dependencyBuilder;
        $this->comparator = $comparator;
        $this->moduleLoader = $moduleLoader;
        $this->systemSetFactory = $systemSetFactory;
    }

    /**
     * Retrieves a list of modules from the given array of $selectedModules that depend on the specified $module.
     *
     * This method identifies and returns all modules within the provided $selectedModules array that have a dependency
     * on the given $module. It returns an array of array entries where each entry consists of a dependent Module and
     * the required version string. This information is useful for understanding and managing module relationships.
     *
     * @param Module $module The module for which dependencies need to be identified.
     * @param Module[] $selectedModules An array of modules to search for dependencies.
     *
     * @return array[]
     *      An array of associative arrays, where each entry has two keys:
     *          - 'module': Represents a Module object.
     *          - 'requiredVersion': Represents a string denoting the required version.
     *
     *      Each entry includes a dependent Module and the required version string.
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
     * Returns a list of Module objects corresponding to a provided Combination.
     *
     * This method takes a Combination object as input and returns an array of Module objects. It is responsible for
     * loading all modules specified in the given Combination, where each module is identified by its archive name and
     * version.
     *
     * @param Combination $combination The Combination object representing a set of modules to be loaded.
     *
     * @return Module[] An array of Module objects that correspond to the modules in the provided Combination.
     *
     * @throws DependencyException
     *      If any module specified in the Combination cannot be found, a DependencyException is thrown with a detailed
     *      error message.
     */
    public function getAllModulesFromCombination(Combination $combination): array
    {
        $this->moduleLoader->resetCache();

        $modules = [];
        foreach ($combination->strip()->getAll() as $archiveName => $version) {
            $module = $this->moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);
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
     * Checks whether a module can be installed and returns a CombinationSatisfyerResult object
     * containing information about the installable combination if the module can be installed.
     *
     * This method allows the verification of the installability of a module with respect to a given SystemSet,
     * optionally excluding specific systems to perform the check. It takes into consideration the module's dependencies
     * and their compatibility with the specified SystemSet. If the module is installable, the returned
     * CombinationSatisfyerResult object provides details about the compatible combination of dependencies.
     *
     * @see SystemSet
     *
     * @param Module $module
     *      The module that needs to be checked for installability.
     *
     * @param string[] $doNotCheck
     *      An array of SystemSet names that should be excluded from the verification. This array is useful when you
     *      want to skip the check for specific systems, such as 'mmlc', 'php', 'modified' or archiveNames like
     *      'robinthehood/stripe' etc. If this array is empty, the method will check all SystemSet dependencies, which
     *      includes all installed module versions and the current PHP, MMLC and modified version.
     *
     * @return CombinationSatisfyerResult
     *      A CombinationSatisfyerResult object that contains the result of the verification. It includes information
     *      about the installability status, the tested combination, the found combination (if installable), and any
     *      associated failure logs.
     *
     * @throws DependencyException
     *      If the module cannot be installed due to conflicting version constraints or missing dependencies, a
     *      DependencyException will be thrown with a detailed error message.
     */
    public function canBeInstalled(Module $module, array $doNotCheck = []): CombinationSatisfyerResult
    {
        $systemSet = $this->systemSetFactory->getSystemSet();

        foreach ($doNotCheck as $name) {
            $systemSet->remove($name);
        }

        $combinationSatisfyerResult = $this->dependencyBuilder->satisfies(
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
        $this->moduleLoader->resetCache();
        $missing = [];

        foreach ($module->getRequire() as $archiveName => $version) {
            $found = false;
            $depModules = $this->moduleLoader->loadAllVersionsByArchiveName($archiveName);
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
