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

namespace RobinTheHood\ModifiedModuleLoaderClient\Tests\Unit\ModuleHahserTests;

use PHPUnit\Framework\TestCase;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\HashFile;

class HashFileTest extends TestCase
{
    public function testGetScope()
    {
        $hashFile = $this->createHashFile();

        $expectedArray = [
            'hashes' => [
                '/dir/file1.php' => md5('code1')
            ]
        ];

        $array = $hashFile->getScope('src');
        $this->assertEquals($expectedArray, $array);
    }

    public function testGetScopeHashes()
    {
        $hashFile = $this->createHashFile();

        $hashEntryCollection = $hashFile->getScopeHashes('src');
        $this->assertCount(1, $hashEntryCollection->hashEntries);
    }

    private function createHashFile()
    {
        $hashFile = new HashFile();
        $hashFile->array = [
            'scopes' => [
                'src' => [
                    'hashes' => [
                        '/dir/file1.php' => md5('code1')
                    ]
                ],
                'src-mmlc' => [
                    'hashes' => [
                        '/vendor-mmlc/dir/file1.php' => md5('code1')
                    ]
                ]
            ]
        ];

        return $hashFile;
    }
}
