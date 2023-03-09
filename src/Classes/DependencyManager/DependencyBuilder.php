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

use RobinTheHood\ModifiedModuleLoaderClient\Loader\ModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Module;

class DependencyBuilder
{
    private function log($value, $file)
    {
        @mkdir(__DIR__ . '/logs/');
        $path = __DIR__ . '/logs/' . $file;
        file_put_contents($path, json_encode($value, JSON_PRETTY_PRINT));
    }

    public function test()
    {
        $moduleLoader = ModuleLoader::getModuleLoader();
        $module = $moduleLoader->loadLatestVersionByArchiveName('firstweb/multi-order');

        $systemSet = new SystemSet();
        $systemSet->systems = [
            "modified" => '2.0.4.2',
            "php" => '7.4.0',
            "mmlc" => '1.19.0',
            "composer/autoload" => '1.3.0',
            "robinthehood/modified-std-module" => '0.9.0',
            "robinthehood/modified-orm" => '1.8.1',
            "robinthehood/pdf-bill" => '0.17.0'
        ];

        var_dump('TEST: satisfiesContraints1');
        $combinationSatisfyerResult = $this->satisfiesContraints1($module, $systemSet);
        var_dump($combinationSatisfyerResult);

        var_dump('TEST: satisfiesContraints2');
        $combinationSatisfyerResult = $this->satisfiesContraints2('firstweb/multi-order', '^1.0.0', $systemSet);
        var_dump($combinationSatisfyerResult);

        var_dump('TEST: satisfiesContraints3');
        $combinationSatisfyerResult = $this->satisfies('firstweb/multi-order', '^1.0.0', $systemSet);
        var_dump($combinationSatisfyerResult);
    }

    public function satisfiesContraints1(Module $module, SystemSet $systemSet): CombinationSatisfyerResult
    {
        $moduleTreeBuilder = new ModuleTreeBuilder();
        $moduleTrees = $moduleTreeBuilder->buildListByConstraints($module);
        $this->log($moduleTrees, '1-moduleTrees.json');

        $flatEntryBuilder = new FlatEntryBuilder();
        $flatEntries = $flatEntryBuilder->buildListFromModuleTrees($moduleTrees);
        $this->log($flatEntries, '1-flatEntries.json');

        $flatEntries = $flatEntryBuilder->fitSystemSet($flatEntries, $systemSet);
        $this->log($flatEntries, '1-flatEntries-fit.json');

        $combinationBuilder = new CombinationBuilder();
        $combinations = $combinationBuilder->buildAllFromModuleFlatEntries($flatEntries);
        $this->log($combinations, '1-combinations.json');

        $combinationSatisfyer = new CombinationSatisfyer();
        $combinationSatisfyerResult = $combinationSatisfyer->satisfiesCominationsFromModuleTrees($moduleTrees, $combinations);

        return $combinationSatisfyerResult;
    }


    public function satisfiesContraints2(string $archiveName, string $constraint, SystemSet $systemSet): CombinationSatisfyerResult
    {
        $moduleTreeBuilder = new ModuleTreeBuilder();
        $moduleTree = $moduleTreeBuilder->buildByConstraints($archiveName, $constraint);
        $this->log($moduleTree, '2-moduleTrees.json');

        $flatEntryBuilder = new FlatEntryBuilder();
        $flatEntries = $flatEntryBuilder->buildListFromModuleTree($moduleTree);
        $this->log($flatEntries, '2-flatEntries.json');

        $flatEntries = $flatEntryBuilder->fitSystemSet($flatEntries, $systemSet);
        $this->log($flatEntries, '2-flatEntries-fit.json');

        $combinationBuilder = new CombinationBuilder();
        $combinations = $combinationBuilder->buildAllFromModuleFlatEntries($flatEntries);
        $this->log($combinations, '2-combinations.json');

        $combinationSatisfyer = new CombinationSatisfyer();
        $combinationSatisfyerResult = $combinationSatisfyer->satisfiesCominationsFromModuleTree($moduleTree, $combinations);

        return $combinationSatisfyerResult;
    }


    public function satisfies(string $archiveName, string $constraint, SystemSet $systemSet): CombinationSatisfyerResult
    {
        $moduleTreeBuilder = new ModuleTreeBuilder();
        $moduleTree = $moduleTreeBuilder->buildByConstraints($archiveName, $constraint);
        $this->log($moduleTree, '3-moduleTrees.json');

        $flatEntryBuilder = new FlatEntryBuilder();
        $flatEntries = $flatEntryBuilder->buildListFromModuleTree($moduleTree);
        $this->log($flatEntries, '3-flatEntries.json');

        $flatEntries = $flatEntryBuilder->fitSystemSet($flatEntries, $systemSet);
        $this->log($flatEntries, '3-flatEntries-fit.json');

        $combinationIterator = new CombinationIterator($flatEntries);
        $combinationSatisfyer = new CombinationSatisfyer();
        $combinationSatisfyerResult = $combinationSatisfyer->satisfiesCominationsFromModuleWithIterator($moduleTree, $combinationIterator);

        return $combinationSatisfyerResult;
    }
}
