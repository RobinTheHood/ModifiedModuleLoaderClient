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

use PHPUnit\Framework\TestCase;
use RobinTheHood\ModifiedModuleLoaderClient\GraphQl\QueryParser;

class QueryParserTest extends TestCase
{

    public function testEmptyQuery()
    {
        $queryParser = new QueryParser();

        $result = $queryParser->parse('');
        $this->assertFalse($result);

        $result = $queryParser->parse('{}');
        $this->assertFalse($result);
    }

    public function testActionGetAll()
    {
        $query = '
            {
                allModules {
                }
            }
        ';


        $queryParser = new QueryParser();
        $result = $queryParser->parse($query);

        $this->assertArrayHasKey('action', $result);
        $this->assertEquals('getAll', $result['action']['method']);
        $this->assertEquals('Module', $result['action']['obj']);
    }

    public function testActionGetAllWithVariables()
    {
        $query = '
            {
                allModules {
                    name
                    age
                }
            }
        ';

        $queryParser = new QueryParser();
        $result = $queryParser->parse($query);

        $this->assertArrayHasKey('action', $result);
        $this->assertEquals('getAll', $result['action']['method']);
        $this->assertEquals('Module', $result['action']['obj']);
        $this->assertEquals('name', $result['action']['variables'][0]['name']);
        $this->assertEquals('age', $result['action']['variables'][1]['name']);
    }

    public function testActionGet()
    {
        $query = '
            {
                Module(firstName: "Robin") {
                }
            }
        ';

        $queryParser = new QueryParser();
        $result = $queryParser->parse($query);

        $this->assertArrayHasKey('action', $result);
        $this->assertEquals('get', $result['action']['method']);
        $this->assertEquals('Module', $result['action']['obj']);
        $this->assertEquals('firstName', $result['action']['conditions'][0]['name']);
        $this->assertEquals('Robin', $result['action']['conditions'][0]['value']);
    }
}
