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

use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\ModuleHashFileCreator;

class ModuleFileInstaller
{
    /**
     * (Re-) Installiert / Überschreibt ein Modul (archive, Version) ohne dabei auf Abhänigkeiten und Modulstatus zu
     * achten. Es wird nur auf Dateiebene kontrolliert, ob alle Dateien geschrieben werden konnten. Die Autoload Datei
     * wird NICHT erzeugt / erneuert.
     */
    public function install(Module $module): void
    {
        $this->installFiles($module);
        $this->createHashFile($module);
    }

    /**
     * Lorem
     */
    public function uninstall(Module $module): void
    {
        $this->uninstallFiles($module);
        $this->deleteHashFile($module);
    }

    /**
     * (Re-) Installiert / Überschreibt nur die Datei zu einem Modul (archive, Version). Es wird nur auf Datei-Ebene
     * kontrolliert, ob alle Dateien geschrieben werden konnten. Die `modulehash.json` Datei wird NICHT erzeugt /
     * erneuert.
     */
    private function installFiles(Module $module): void
    {
        // Install Source Files to Shop Root
        $files = $module->getSrcFilePaths();

        foreach ($files as $file) {
            $src = $module->getLocalRootPath() . $module->getSrcRootPath() . '/' . $file;

            // TODO: Kontrollieren, könnte es Probleme machen, dass $files hier noch einmal zugewiesen / gesetzt wird?
            $files = $module->getTemplateFiles($file);
            foreach ($files as $file) {
                $overwrite = false;
                if (!FileInfo::isTemplateFile($file)) {
                    $overwrite = true;
                }

                $file = ModulePathMapper::moduleSrcToShopRoot($file);

                $dest = App::getShopRoot() . $file;
                $this->installFile($src, $dest, $overwrite);
            }
        }

        // Install Source Mmlc Files to shop vendor-mmlc
        $files = $module->getSrcMmlcFilePaths();
        foreach ($files as $file) {
            $src = $module->getLocalRootPath() . $module->getSrcMmlcRootPath() . '/' . $file;
            $file = ModulePathMapper::moduleSrcMmlcToShopVendorMmlc($file, $module->getArchiveName());
            $dest = App::getShopRoot() . '/' . $file;
            $this->installFile($src, $dest, true);
        }
    }

    private function installFile(string $src, string $dest, bool $overwrite = false): bool
    {
        if (!file_exists($src)) {
            return false;
        }

        if ($overwrite == false && (file_exists($dest) || is_link($dest))) {
            return false;
        } elseif ($overwrite == true && (file_exists($dest) || is_link($dest))) {
            unlink($dest);
        }

        FileHelper::makeDirIfNotExists($dest);

        if (file_exists($dest) || is_link($dest)) {
            return false;
        }

        if (Config::getInstallMode() == 'link') {
            symlink($src, $dest);
        } else {
            copy($src, $dest);
        }

        return true;
    }

    /**
     * Erzeugt / Überschreibt die `modulehash.json zu einem Modul (archive, Version)` Es wird nur auf Datei-Ebene
     * kontrolliert, ob alle Dateien geschrieben werden konnten.
     */
    private function createHashFile(Module $module): void
    {
        $moduleHashFileCreator = new ModuleHashFileCreator();
        $moduleHashFile = $moduleHashFileCreator->createHashFile($module);
        $moduleHashFile->writeTo($module->getHashPath());
    }

    private function uninstallFiles(Module $module): void
    {
        // Uninstall from shop-root
        $files = $module->getSrcFilePaths();
        foreach ($files as $file) {
            $file = ModulePathMapper::moduleSrcToShopRoot($file);
            $dest = App::getShopRoot() . $file;
            $this->uninstallFile($dest);
        }

        // Uninstall from shop-vendor-mmlc
        $files = $module->getSrcMmlcFilePaths();
        foreach ($files as $file) {
            $file = ModulePathMapper::moduleSrcMmlcToShopVendorMmlc($file, $module->getArchiveName());
            $dest = App::getShopRoot() . $file;
            $this->uninstallFile($dest);
            FileHelper::deletePathIsEmpty($dest);
        }
    }

    private function uninstallFile(string $dest): void
    {
        if (file_exists($dest)) {
            unlink($dest);
        }
    }

    private function deleteHashFile(Module $module): void
    {
        if (file_exists($module->getHashPath())) {
            unlink($module->getHashPath());
        }
    }
}
