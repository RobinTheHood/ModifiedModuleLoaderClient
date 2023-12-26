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

namespace RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher;

use RobinTheHood\ModifiedModuleLoaderClient\FileHasher\FileHasher;
use RobinTheHood\ModifiedModuleLoaderClient\FileHasher\HashFile;
use RobinTheHood\ModifiedModuleLoaderClient\Module;

class ModuleHashFileCreator
{
    /** @var ModuleHasher */
    private $moduleHasher;

    public static function create(): ModuleHashFileCreator
    {
        return new ModuleHashFileCreator();
    }

    public function __construct()
    {
        $this->moduleHasher = new ModuleHasher(new FileHasher());
    }

    public function createHashFile(Module $module): HashFile
    {
        $shopHashEntryCollection = $this->moduleHasher->createShopRootHashes($module);
        $shopVendorMmlcEntryCollection = $this->moduleHasher->createShopVendorMmlcHashes($module);

        $array = [
            'version' => '0.2.0',
            'scopes' => [
                ModuleHasher::SCOPE_SHOP_ROOT => [
                    'hashes' => $shopHashEntryCollection->toArray()
                ],
                ModuleHasher::SCOPE_SHOP_VENDOR_MMLC => [
                    'hashes' => $shopVendorMmlcEntryCollection->toArray()
                ]
            ]
        ];

        $hashFile = new HashFile();
        $hashFile->array = $array;

        return $hashFile;
    }
}
