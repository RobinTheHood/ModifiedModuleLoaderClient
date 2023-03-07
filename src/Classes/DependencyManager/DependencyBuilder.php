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

        $constraints = [
            // "modified" => ['2.0.4.2'],
            "composer/autoload" => ['1.3.0'],
            // "robinthehood/modified-std-module" => ['0.9.0'],
            // "robinthehood/modified-orm" => ['1.8.1'],
            // "robinthehood/pdf-bill" => ['0.17.0']
        ];

        var_dump('TEST: satisfiesContraints1');
        $this->satisfiesContraints1(
            $module,
            $constraints
        );

        var_dump('TEST: satisfiesContraints2');
        $this->satisfiesContraints2(
            'firstweb/multi-order',
            '^1.13.0',
            $constraints
        );

        var_dump('TEST: satisfiesContraints3');
        $this->satisfiesContraints3(
            'firstweb/multi-order',
            '^1.0.0',
            $constraints
        );
    }

    public function satisfiesContraints1(Module $module, array $contraints): void
    {
        $moduleTreeBuilder = new ModuleTreeBuilder();
        $moduleTrees = $moduleTreeBuilder->buildListByConstraints($module);
        $this->log($moduleTrees, '1-moduleTrees.json');

        $flatEntryBuilder = new FlatEntryBuilder();
        $flatEntries = $flatEntryBuilder->buildListFromModuleTrees($moduleTrees);
        $this->log($flatEntries, '1-flatEntries.json');

        $flatEntries = $flatEntryBuilder->removeFlatEntriesByContrains($flatEntries, $contraints);
        $this->log($flatEntries, '1-flatEntries-removed.json');

        $combinationBuilder = new CombinationBuilder();
        $combinations = $combinationBuilder->buildAllFromModuleFlatEntries($flatEntries);
        $this->log($combinations, '1-combinations.json');

        $combinationSatisfyer = new CombinationSatisfyer();
        $combination = $combinationSatisfyer->satisfiesCominationsFromModuleTrees($moduleTrees, $combinations);
        var_dump($combination);
    }


    public function satisfiesContraints2(string $archiveName, string $constraint, array $contraints): void
    {
        $moduleTreeBuilder = new ModuleTreeBuilder();
        $moduleTree = $moduleTreeBuilder->buildByConstraints($archiveName, $constraint);
        $this->log($moduleTree, '2-moduleTrees.json');

        $flatEntryBuilder = new FlatEntryBuilder();
        $flatEntries = $flatEntryBuilder->buildListFromModuleTree($moduleTree);
        $this->log($flatEntries, '2-flatEntries.json');

        $flatEntries = $flatEntryBuilder->removeFlatEntriesByContrains($flatEntries, $contraints);
        $this->log($flatEntries, '2-flatEntries-removed.json');

        $combinationBuilder = new CombinationBuilder();
        $combinations = $combinationBuilder->buildAllFromModuleFlatEntries($flatEntries);
        // $this->log($combinations, '2-combinations.json');

        $combinationSatisfyer = new CombinationSatisfyer();
        $combination = $combinationSatisfyer->satisfiesCominationsFromModuleTree($moduleTree, $combinations);
        var_dump($combination);
    }


    public function satisfiesContraints3(string $archiveName, string $constraint, array $contraints): void
    {
        $moduleTreeBuilder = new ModuleTreeBuilder();
        $moduleTree = $moduleTreeBuilder->buildByConstraints($archiveName, $constraint);
        $this->log($moduleTree, '3-moduleTrees.json');

        $flatEntryBuilder = new FlatEntryBuilder();
        $flatEntries = $flatEntryBuilder->buildListFromModuleTree($moduleTree);
        $this->log($flatEntries, '3-flatEntries.json');

        $flatEntries = $flatEntryBuilder->removeFlatEntriesByContrains($flatEntries, $contraints);
        $this->log($flatEntries, '3-flatEntries-removed.json');

        $combinationIterator = new CombinationIterator($flatEntries);
        $combinationSatisfyer = new CombinationSatisfyer();
        $combination = $combinationSatisfyer->satisfiesCominationsFromModuleWithIterator($moduleTree, $combinationIterator);
        var_dump($combination);
    }
}
