<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient;

use RobinTheHood\ModifiedModuleLoaderClient\Helpers\Hasher;

class ModuleHasher extends Hasher
{
    public function hashModule($module)
    {
        $hashFilePath = $module->getHashPath();
        $hashes = $this->createModuleHashes($module);
        $this->createHashFile($hashFilePath, $hashes);
    }

    public function unhashModule($module)
    {
        $hashFilePath = $module->getHashPath();
        $this->deleteHashFile($hashFilePath);
    }

    public function createModuleHashes($module, $moduleDir = false)
    {
        if ($moduleDir) {
            $root = $module->getLocalRootPath() . $module->getSrcRootPath() . '/';
        } else {
            $root = App::getShopRoot();
        }

        $files = $module->getSrcFilePaths();
        $hashes = $this->createFileHashes($files, $root);
        return $hashes;
    }

    public function loadeModuleHashes($module)
    {
        $hashFilePath = $module->getHashPath();
        $hashes = $this->loadHashes($hashFilePath);
        return $hashes;
    }

    public function getModuleChanges($module)
    {
        $hashesLoaded = $this->loadeModuleHashes($module);
        $hashesCreatedA = $this->createModuleHashes($module);
        $hashesCreatedB = $this->createModuleHashes($module, true);

        return $this->getChanges($hashesLoaded, $hashesCreatedA, $hashesCreatedB);
    }
}
