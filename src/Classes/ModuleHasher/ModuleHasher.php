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

use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\ModulePathMapper;

class ModuleHasher
{
    public const SCOPE_MODULE_SRC = 'module-src';
    public const SCOPE_MODULE_SRC_MMLC = 'module-src-mmlc';
    public const SCOPE_SHOP_ROOT = 'shop-root';
    public const SCOPE_SHOP_VENDOR_MMLC = 'shop-vendor-mmlc';

    /** @var FileHasherInterface $hasher */
    private $fileHasher;

    public function __construct(FileHasherInterface $fileHasher)
    {
        $this->fileHasher = $fileHasher;
    }

    /**
     * <SHOPROOT>/Module/<VENDOR>/<MODULE>/<SRC>/...
     */
    public function createModuleSrcHashes(Module $module): HashEntryCollection
    {
        $files = $module->getSrcFilePaths();
        $root = $module->getLocalRootPath() . $module->getSrcRootPath() . '/';
        return  $this->fileHasher->createHashes($files, $root, self::SCOPE_MODULE_SRC);
    }

    /**
     * <SHOPROOT>/Module/<VENDOR>/<MODULE>/<SRC-MMLC>/...
     */
    public function createModuleSrcMmlcHashes(Module $module): HashEntryCollection
    {
        return new HashEntryCollection([]);

        // TODO: Warte auf feat/vendor-mmlc
        // $files = $module->getSrcMmlcFilePaths();
        // $root = $module->getLocalRootPath() . $module->getSrcMmlcRootPath() . '/';
        // return $this->fileHasher->createHashes($files, $root, self::SCOPE_MODULE_SRC_MMLC);
    }

    /**
     * <SHOPROOT>/...
     */
    public function createShopRootHashes(Module $module): HashEntryCollection
    {
        $files = $module->getSrcFilePaths();
        $root = App::getShopRoot();
        $files = ModulePathMapper::mmlcPathsToShopPaths($files);
        //$files = ModulePathMapper::srcPathsToShopPaths($files);
        return $this->fileHasher->createHashes($files, $root, self::SCOPE_SHOP_ROOT);
    }

    /**
     * <SHOPROOT>/vendor-mmlc/<VENDOR>/<MODULE>/...
     */
    public function createShopVendorMmlcHashes(Module $module): HashEntryCollection
    {
        return new HashEntryCollection([]);

        // TODO: Warte auf feat/vendor-mmlc
        // $files = $module->getSrcMmlcFilePaths();
        // $root = App::getShopRoot();
        // $files = ModulePathMapper::srcMmlcPathsToVendorMmlcPaths($files);
        // return $this->fileHasher->createHashes($files, $root, self::SCOPE_SHOP_VENDOR_MMLC);
    }
}
