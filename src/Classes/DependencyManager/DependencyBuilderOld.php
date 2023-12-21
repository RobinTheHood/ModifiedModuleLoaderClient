<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient\DependencyManager;

use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\Config;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\ModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Constraint;

class DependencyBuilderOld
{
    /** @var ModuleTreeBuilder*/
    private $moduleTreeBuilder;

    /** @var ModuleLoader*/
    private $moduleLoader;

    public static function create(int $mode): DependencyBuilder
    {
        $moduleTreeBuilder = ModuleTreeBuilder::create($mode);
        $moduleLoader = ModuleLoader::create($mode);
        $dependencyBuilder = new DependencyBuilder($moduleTreeBuilder, $moduleLoader);
        return $dependencyBuilder;
    }

    public function __construct(ModuleTreeBuilder $moduleTreeBuilder, ModuleLoader $moduleLoader)
    {
        $this->moduleTreeBuilder = $moduleTreeBuilder;
        $this->moduleLoader = $moduleLoader;
    }

    private function logFile($value, $file)
    {
        if (!Config::getLogging()) {
            return;
        }

        $logsRootPath = App::getLogsRoot();

        @mkdir($logsRootPath);
        @mkdir($logsRootPath . '/debug');
        @mkdir($logsRootPath . '/debug/DependencyMananger/');
        $path = $logsRootPath . '/debug/DependencyMananger/' . $file;
        file_put_contents($path, json_encode($value, JSON_PRETTY_PRINT));
    }

    public function log($var)
    {
        print_r($var);
    }

    public function test()
    {
        $moduleLoader = ModuleLoader::create(Comparator::CARET_MODE_STRICT);
        $module = $moduleLoader->loadLatestVersionByArchiveName('firstweb/multi-order');

        if (!$module) {
            die('Can not find base module');
        }

        $systemSet = new SystemSet();
        $systemSet->set([
            "modified" => '2.0.4.2',
            "php" => '7.4.0',
            "mmlc" => '1.19.0',
            "composer/autoload" => '1.3.0',
            "robinthehood/modified-std-module" => '0.9.0',
            "robinthehood/modified-orm" => '1.8.1',
            "robinthehood/pdf-bill" => '0.17.0'
        ]);

        $this->log('TEST: satisfiesContraints1');
        $combinationSatisfyerResult = $this->satisfiesContraints1($module, $systemSet);
        $this->log($combinationSatisfyerResult);

        $this->log('TEST: satisfiesContraints2');
        $combinationSatisfyerResult = $this->satisfiesContraints2('firstweb/multi-order', '^1.0.0', $systemSet);
        $this->log($combinationSatisfyerResult);

        // var_dump('TEST: satisfiesContraints3');
        $combinationSatisfyerResult = $this->satisfies('firstweb/multi-order', '^1.0.0', $systemSet);
        $this->log($combinationSatisfyerResult);
    }

    public function satisfiesContraints1(Module $module, SystemSet $systemSet): CombinationSatisfyerResult
    {
        $moduleTrees = $this->moduleTreeBuilder->buildListByConstraints($module);
        $this->logFile($moduleTrees, '1-moduleTrees.json');

        $flatEntryBuilder = new FlatEntryBuilder();
        $flatEntries = $flatEntryBuilder->buildListFromModuleTrees($moduleTrees);
        $this->logFile($flatEntries, '1-flatEntries.json');

        $flatEntries = $flatEntryBuilder->fitSystemSet($flatEntries, $systemSet);
        $this->logFile($flatEntries, '1-flatEntries-fit.json');

        $combinationBuilder = new CombinationBuilder();
        $combinations = $combinationBuilder->buildAllFromModuleFlatEntries($flatEntries);
        $this->logFile($combinations, '1-combinations.json');

        $combinationSatisfyer = new CombinationSatisfyer();
        $combinationSatisfyerResult = $combinationSatisfyer->satisfiesCominationsFromModuleTrees(
            $moduleTrees,
            $combinations
        );

        return $combinationSatisfyerResult;
    }


    public function satisfiesContraints2(
        string $archiveName,
        string $constraint,
        SystemSet $systemSet
    ): CombinationSatisfyerResult {
        $moduleTree = $this->moduleTreeBuilder->buildByConstraints($archiveName, $constraint);
        $this->logFile($moduleTree, '2-moduleTrees.json');

        $flatEntryBuilder = new FlatEntryBuilder();
        $flatEntries = $flatEntryBuilder->buildListFromModuleTree($moduleTree);
        $this->logFile($flatEntries, '2-flatEntries.json');

        $flatEntries = $flatEntryBuilder->fitSystemSet($flatEntries, $systemSet);
        $this->logFile($flatEntries, '2-flatEntries-fit.json');

        $combinationBuilder = new CombinationBuilder();
        $combinations = $combinationBuilder->buildAllFromModuleFlatEntries($flatEntries);
        $this->logFile($combinations, '2-combinations.json');

        $combinationSatisfyer = new CombinationSatisfyer();
        $combinationSatisfyerResult = $combinationSatisfyer->satisfiesCominationsFromModuleTree(
            $moduleTree,
            $combinations
        );

        return $combinationSatisfyerResult;
    }

    public function satisfies(string $archiveName, string $constraint, SystemSet $systemSet): CombinationSatisfyerResult
    {
        $systemSet->remove($archiveName);
        $constraint = $this->createConstraint($archiveName, $constraint, $systemSet);

        $moduleTree = $this->moduleTreeBuilder->buildByConstraints($archiveName, $constraint);
        $this->logFile($moduleTree, '3-moduleTrees.json');

        $flatEntryBuilder = new FlatEntryBuilder();
        $flatEntries = $flatEntryBuilder->buildListFromModuleTree($moduleTree);
        $this->logFile($flatEntries, '3-flatEntries.json');

        $flatEntries = $flatEntryBuilder->fitSystemSet($flatEntries, $systemSet);
        $this->logFile($flatEntries, '3-flatEntries-fit.json');

        $combinationIterator = new CombinationIterator($flatEntries);
        $combinationSatisfyer = new CombinationSatisfyer();
        $combinationSatisfyerResult = $combinationSatisfyer->satisfiesCombinationsFromModuleWithIterator(
            $moduleTree,
            $combinationIterator
        );
        return $combinationSatisfyerResult;
    }

    /**
     * Gehe alle Module durch, die in $systemSet sind und das gleichzeitig Modul $archiveName benötigen.
     * Gibt ein $constraint zurück, sodass die Anforderungenden der Module in $systemSet erhaltenbleiben.
     */
    private function createConstraint(string $archiveName, string $constraint, SystemSet $systemSet): string
    {
        /** @var string[] */
        $requiredConstraints = [$constraint];

        $archives = $systemSet->getArchives();
        foreach ($archives as $archiveNameB => $version) {
            $installedModule = $this->getModuleByArchiveNameAndVersion($archiveNameB, $version);
            if (!$installedModule) {
                continue;
            }

            $requiredConstraint = $this->getRequiredConstraint($installedModule, $archiveName);
            if (!$requiredConstraint) {
                continue;
            }

            $requiredConstraints[] = $requiredConstraint;
        }

        $constraint = Constraint::createConstraintFromConstraints($requiredConstraints);

        return $constraint;
    }

    private function getModuleByArchiveNameAndVersion(string $archiveName, string $version): ?Module
    {
        return $this->moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);
    }

    private function getRequiredConstraint(Module $installedModule, string $archiveName): string
    {
        $required = $installedModule->getRequire();
        return $required[$archiveName] ?? '';
    }
}
