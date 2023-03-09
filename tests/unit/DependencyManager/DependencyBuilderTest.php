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

namespace RobinTheHood\ModifiedModuleLoaderClient\Tests\Unit\DependencyManager;

use PHPUnit\Framework\TestCase;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\DependencyBuilder;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\SystemSet;

class DependencyBuilderTest extends TestCase
{
    public function testSatisfies()
    {
        $dependencyBuilder = new DependencyBuilder();
        $systemSet = new SystemSet();

        $systemSet->systems = [
            "modified" => '2.0.4.2',
            "php" => '7.4.0',
            "mmlc" => '1.18.0',
            "composer/autoload" => '1.3.0',
            "robinthehood/modified-std-module" => '0.9.0',
            "robinthehood/modified-orm" => '1.8.1',
            "robinthehood/pdf-bill" => '0.17.0',
            "foo/bar" => '1.2.3'
        ];

        $combinationSatisfyerResult = $dependencyBuilder->satisfies('firstweb/multi-order', '^1.0.0', $systemSet);

        $this->assertEqualsCanonicalizing(
            [
                "modified" => '2.0.4.2',
                "php" => '7.4.0',
                "mmlc" => '1.18.0',
                "composer/autoload" => '1.3.0',
                "robinthehood/modified-std-module" => '0.9.0',
                "robinthehood/modified-orm" => '1.8.1',
                "robinthehood/modified-ui" => '0.1.0',
                "robinthehood/pdf-bill" => '0.17.0',
                "robinthehood/tfpdf" => '0.3.0',
                'firstweb/multi-order' => '1.13.3',
                "foo/bar" => '1.2.3'
            ],
            $combinationSatisfyerResult->testCombination->combinations
        );

        $this->assertEqualsCanonicalizing(
            [
                "modified" => '2.0.4.2',
                "composer/autoload" => '1.3.0',
                "robinthehood/modified-std-module" => '0.9.0',
                "robinthehood/modified-orm" => '1.8.1',
                "robinthehood/modified-ui" => '0.1.0',
                "robinthehood/pdf-bill" => '0.17.0',
                "robinthehood/tfpdf" => '0.3.0',
                'firstweb/multi-order' => '1.13.3',
            ],
            $combinationSatisfyerResult->foundCombination->combinations
        );
    }

    public function atestInvokeDependency()
    {
        $dpb = new DependencyBuilder();
        $dpb->test();
        die('TEST DONE');
    }
}
