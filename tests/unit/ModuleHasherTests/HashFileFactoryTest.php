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
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\HashFileFactory;

class HashFileFactoryTest extends TestCase
{
    public function testCreateFromJson()
    {
        $hashFile = HashFileFactory::createFromJson('{
            "/dir/testfile.php": "hash"
        }');

        $this->assertEquals('0.2.0', $hashFile->array['version']);
    }
}