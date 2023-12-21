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

namespace RobinTheHood\ModifiedModuleLoaderClient;

use RobinTheHood\ModifiedModuleLoaderClient\FileHasher\ChangedEntry;
use RobinTheHood\ModifiedModuleLoaderClient\FileHasher\ChangedEntryCollection;
use RobinTheHood\ModifiedModuleLoaderClient\FileHasher\FileHasher;
use RobinTheHood\ModifiedModuleLoaderClient\FileHasher\HashFile;
use RobinTheHood\ModifiedModuleLoaderClient\FileHasher\HashFileLoader;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\Comparator;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\ModuleHasher;

class ModuleChangeManager
{
    /**
     * HIER FEHLT EINE BESCHREIBUNG
     *
     * @return ChangedEntryCollection
     */
    public static function getChangedFiles(Module $module): ChangedEntryCollection
    {
        $hashFileLoader = new HashFileLoader();
        $hashFileLoader->setDefaultScope(ModuleHasher::SCOPE_SHOP_ROOT);
        $hashFile = $hashFileLoader->load($module->getHashPath());

        if (!$hashFile) {
            return new ChangedEntryCollection([]);
        }

        $srcChangedEntiresCollection = self::getSrcChanges($module, $hashFile);
        $srcMmlcChangedEntiresCollection = self::getSrcMmlcChanges($module, $hashFile);

        $changedEntiresCollection = ChangedEntryCollection::merge([
            $srcChangedEntiresCollection,
            $srcMmlcChangedEntiresCollection
        ]);

        return $changedEntiresCollection;
    }

    private static function getSrcChanges(Module $module, HashFile $hashFile): ChangedEntryCollection
    {
        $moduleHasher = new ModuleHasher(new FileHasher());

        $installedHashes = $hashFile->getScopeHashes(ModuleHasher::SCOPE_SHOP_ROOT);
        $shopHashes = $moduleHasher->createShopRootHashes($module);
        $srcHashes = $moduleHasher->createModuleSrcHashes($module);

        $comparator = new Comparator();
        $changedEntiresCollection = $comparator->getChangedEntries($installedHashes, $shopHashes, $srcHashes);

        return $changedEntiresCollection;
    }

    private static function getSrcMmlcChanges(Module $module, HashFile $hashFile): ChangedEntryCollection
    {
        $moduleHasher = new ModuleHasher(new FileHasher());

        $installedHashes = $hashFile->getScopeHashes(ModuleHasher::SCOPE_SHOP_VENDOR_MMLC);
        $vendorMmlcHashes = $moduleHasher->createShopVendorMmlcHashes($module);
        $srcMmlcHashes = $moduleHasher->createModuleSrcMmlcHashes($module);

        $comparator = new Comparator();
        $changedEntiresCollection = $comparator->getChangedEntries($installedHashes, $vendorMmlcHashes, $srcMmlcHashes);

        return $changedEntiresCollection;
    }

    public static function getFileChanges(Module $module, ChangedEntry $changedEntry)
    {
        if ($changedEntry->type !== ChangedEntry::TYPE_CHANGED) {
            return '';
        }

        $moduleSrcFilePath = '';
        $installedFilePath = '';

        if (
            $changedEntry->hashEntryA->scope === ModuleHasher::SCOPE_SHOP_ROOT
            || $changedEntry->hashEntryA->scope === ModuleHasher::SCOPE_MODULE_SRC
        ) {
            $moduleSrcFilePath =
                $module->getLocalRootPath() . $module->getSrcRootPath() . '/' . $changedEntry->hashEntryA->file;
            $installedFilePath =
                App::getShopRoot() . '/' . ModulePathMapper::moduleSrcToShopRoot($changedEntry->hashEntryA->file);
        } elseif (
            $changedEntry->hashEntryA->scope === ModuleHasher::SCOPE_SHOP_VENDOR_MMLC
            || $changedEntry->hashEntryA->scope === ModuleHasher::SCOPE_MODULE_SRC_MMLC
        ) {
            // TODO
            $moduleSrcFilePath =
                $module->getLocalRootPath() . $module->getSrcMmlcRootPath() . '/' . $changedEntry->hashEntryA->file;
            $installedFilePath =
                App::getShopRoot() . '/' . ModulePathMapper::moduleSrcMmlcToShopVendorMmlc(
                    $changedEntry->hashEntryA->file,
                    $module->getArchiveName()
                );
        }


        if (file_exists($installedFilePath) && is_link($installedFilePath)) {
            return "No line by line diff available for linked files, because they have always equal content.";
        }

        $moduleSrcFileContent = '';
        if (file_exists($moduleSrcFilePath)) {
            $moduleSrcFileContent = file_get_contents($moduleSrcFilePath);
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
        return $differ->diff($moduleSrcFileContent, $installedFileContent);
    }
}
