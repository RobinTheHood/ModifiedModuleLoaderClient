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
    /** @var FileHasherInterface $hasher */
    private $fileHasher;

    public function __construct(FileHasherInterface $fileHasher)
    {
        $this->fileHasher = $fileHasher;
    }

    /**
     * <SHOPROOT>/Module/<VENDOR>/<MODULE>/<SRC>/...
     */
    public function createSrcHashes(Module $module): HashEntryCollection
    {
        $files = $module->getSrcFilePaths();
        $root = $module->getLocalRootPath() . $module->getSrcRootPath() . '/';
        return  $this->fileHasher->createHashes($files, $root);
    }

    /**
     * <SHOPROOT>/Module/<VENDOR>/<MODULE>/<SRC-MMLC>/...
     */
    public function createSrcMmlcHashes(Module $module): HashEntryCollection
    {
        return new HashEntryCollection([]);

        // TODO: Warte auf feat/vendor-mmlc
        // $files = $module->getSrcMmlcFilePaths();
        // $root = $module->getLocalRootPath() . $module->getSrcMmlcRootPath() . '/';
        // return $this->fileHasher->createHashes($files, $root);
    }

    /**
     * <SHOPROOT>/...
     */
    public function createShopHashes(Module $module): HashEntryCollection
    {
        $files = $module->getSrcFilePaths();
        $root = App::getShopRoot();
        $files = ModulePathMapper::mmlcPathsToShopPaths($files);
        //$files = ModulePathMapper::srcPathsToShopPaths($files);
        return $this->fileHasher->createHashes($files, $root);
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
        // return $this->fileHasher->createHashes($files, $root);
    }
}
