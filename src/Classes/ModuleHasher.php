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
        $files = $module->getSrcFilePaths();

        if ($moduleDir) {
            $root = $module->getLocalRootPath() . $module->getSrcRootPath() . '/';
        } else {
            $root = App::getShopRoot();
            $files = ModulePathMapper::mmlcPathsToShopPaths($files);
        }

        $hashes = $this->createFileHashes($files, $root);
        
        if (!$moduleDir) {
            $hashes = $this->mapHashesShopToMmlc($hashes);
        }

        return $hashes;
    }

    public function mapHashesShopToMmlc($hashes)
    {
        $mappedHashes = [];
        foreach ($hashes as $file => $hash) {
            $file = ModulePathMapper::shopToMmlc($file);
            $mappedHashes[$file] = $hash;
        }
        return $mappedHashes;
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

    public static function getFileChanges($module, $path, $mode = 'changed')
    {
        if ($mode != 'changed') {
            return '';
        }

        $moduleFilePath = $module->getLocalRootPath() . $module->getSrcRootPath() . '/' . $path;
        $installedFilePath = App::getShopRoot() . '/' . ModulePathMapper::mmlcToShop($path);;

        if (file_exists($installedFilePath) && is_link($installedFilePath)) {
            return "No line by line diff available for linked files, because they have always equal content.";
        }

        $moduleFileContent = '';
        if (file_exists($moduleFilePath)) {
            $moduleFileContent = file_get_contents($moduleFilePath);
        }

        $installedFileContent = '';
        if (file_exists($installedFilePath)) {
            $installedFileContent = file_get_contents($installedFilePath);
        }

        $builder = new \SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder(
            "--- Original\n+++ New\n",  // custom header
            true                        // show line numbers 
        );

        $differ = new \SebastianBergmann\Diff\Differ($builder);
        return $differ->diff($moduleFileContent, $installedFileContent);
    }
}
