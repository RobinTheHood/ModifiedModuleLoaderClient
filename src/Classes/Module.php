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

use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\ShopInfo;
use RobinTheHood\ModifiedModuleLoaderClient\FileInfo;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFilter;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleInfo;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\ModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\ChangedEntryCollection;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\Comparator;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\HashFile;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\HashFileLoader;

class Module extends ModuleInfo
{
    /**
     * @var string
     */
    private string $localRootPath;

    /**
     * @var string
     */
    private string $urlRootPath;

    /**
     * @var string
     */
    private string $modulePath;

    /**
     * @var string
     */
    private string $iconPath;

    /**
     * @var string[]
     */
    private array $imagePaths;

    /**
     * @var string[]
     */
    private array $docFilePaths;

    /**
     * @var string
     */
    private string $changelogPath;

    /**
     * @var string
     */
    private string $readmePath;

    /**
     * @var string[]
     */
    private array $srcFilePaths;

    /**
     * @var bool
     */
    private bool $isRemote;

    /**
     * @var bool
     */
    private bool $isLoadable;

    /**
     * Liefert den absoluten Pfad zum MMLC
     * MMLC-Root Verzeichnis.
     *
     * Beispiel:
     * /root/dir1/dir2/.../ModifiedModuleLoaderClient
     */
    public function getLocalRootPath(): string
    {
        return $this->localRootPath;
    }

    public function setLocalRootPath(string $value): void
    {
        $this->localRootPath = $value;
    }

    /**
     * Wenn es sich um ein Remot - Modul handelt, wird die
     * URL des Servers zurückgeliefert, unter der Server
     * erreichbar ist.
     *
     * Beispiel:
     * http://app.module-loader.localhost
     */
    public function getUrlRootPath(): string
    {
        return $this->urlRootPath;
    }

    public function setUrlRootPath(string $value): void
    {
        $this->urlRootPath = $value;
    }

    /**
     * Liefert den Pfad zum Module relativ zum
     * MMLC-Root Verzeichnis.
     *
     * Beispiel:
     * /Modules/{VENDOR-NAME}/{MODULE-NAME}
     */
    public function getModulePath(): string
    {
        return $this->modulePath;
    }

    public function setModulePath(string $value): void
    {
        $this->modulePath = $value;
    }

    /**
     * Liefert den Pfad zum Module-Icon relativ zum
     * MMLC-Root Verzeichnis.
     *
     * Beispiel:
     * /Modules/{VENDOR-NAME}/{MODULE-NAME}/icon.png
     */
    public function getIconPath(): string
    {
        return $this->iconPath;
    }

    public function setIconPath(string $iconPath): void
    {
        $this->iconPath = $iconPath;
    }

    /**
     * Liefert Imagepfade relativ zum
     * MMLC-Root Verzeichnis.
     *
     * Beispiel: [
     *  /Modules/{VENDOR-NAME}/{MODULE-NAME}/images/image1.jpg
     *  /Modules/{VENDOR-NAME}/{MODULE-NAME}/images/image2.jpg
     * ]
     *
     * @return string[]
     */
    public function getImagePaths(): array
    {
        return $this->imagePaths;
    }

    /**
     * @param string[] $imagePaths
     */
    public function setImagePaths(array $imagePaths): void
    {
        $this->imagePaths = $imagePaths;
    }

    /**
     * Liefert Imagepfade relativ zum
     * MMLC-Root Verzeichnis.
     *
     * Beispiel: [
     *  /Modules/{VENDOR-NAME}/{MODULE-NAME}/docs/install.md
     *  /Modules/{VENDOR-NAME}/{MODULE-NAME}/docs/usage.md
     * ]
     *
     * @return string[]
     */
    public function getDocFilePaths(): array
    {
        return $this->docFilePaths;
    }

    /**
     * @param string[] $docFilePaths
     */
    public function setDocFilePaths(array $docFilePaths): void
    {
        $this->docFilePaths = $docFilePaths;
    }

    /**
     * Liefert den Pfad zur changelog.md relativ zum
     * MMLC-Root Verzeichnis.
     *
     * Beispiel:
     * /Modules/{VENDOR-NAME}/{MODULE-NAME}/changelog.md
     *
     * @return string
     */
    public function getChangelogPath(): string
    {
        return $this->changelogPath;
    }

    public function setChangelogPath(string $changelogPath): void
    {
        $this->changelogPath = $changelogPath;
    }

    /**
     * Liefert den Pfad zur readme.md relativ zum
     * MMLC-Root Verzeichnis.
     *
     * Beispiel:
     * /Modules/{VENDOR-NAME}/{MODULE-NAME}/readme.md
     */
    public function getReadmePath(): string
    {
        return $this->readmePath;
    }

    public function setReadmePath(string $readmePath): void
    {
        $this->readmePath = $readmePath;
    }

    /**
     * Liefert ein Array mit Dateienpfaden, die sich in 'new_files'
     * befinden.
     *
     * Beispiel: [
     *  /admin/includes/rth_file1.php
     *  /includes/rth_file1.php
     *  /includes/extra/rth_file1.php
     * ]
     */
    public function getSrcFilePaths(): array
    {
        return $this->srcFilePaths;
    }

    public function setSrcFilePaths(array $value): void
    {
        $this->srcFilePaths = $value;
    }

    /**
     * Liefert true, wenn es sich um ein Remote Modul handelt.
     */
    public function isRemote(): bool
    {
        return $this->isRemote;
    }

    public function setRemote(bool $value): void
    {
        $this->isRemote = $value;
    }

    /**
     * Liefert true, wenn das Module geladen werden darf/kann.
     */
    public function isLoadable()
    {
        return $this->isLoadable;
    }

    public function setLoadable($value)
    {
        $this->isLoadable = $value;
    }

    /**
     * HIER FEHLT EINE BESCHREIBUNG
     *
     */
    public function getUrlOrLocalRootPath(): string
    {
        if ($this->isRemote()) {
            return $this->getUrlRootPath();
        } else {
            return $this->getLocalRootPath();
        }
    }

    /**
     * HIER FEHLT EINE BESCHREIBUNG
     *
     * /Modules/{VENDOR-NAME}/{MODULE-NAME}/new_files
     */
    public function getSrcRootPath(): string
    {
        return $this->getModulePath() . '/' . $this->getSourceDir();
    }

    /**
     * HIER FEHLT EINE BESCHREIBUNG
     *
     * ...shop.de/Modules/{VENDOR-NAME}/{MODULE-NAME}/icon.xxx
     */
    public function getIconUri(): string
    {
        return $this->getUrlRootPath() . $this->getIconPath();
    }

    public function getImageUris()
    {
        return array_map(
            function ($value) {
                return $this->getUrlRootPath() . $value;
            },
            $this->getImagePaths()
        );
    }

    public function getDocFilePath($fileName)
    {
        foreach ($this->getDocFilePaths() as $docFilePath) {
            if (\substr_count($docFilePath, $fileName)) {
                return $docFilePath;
            }
        }
    }

    /**
     * Liefert die install.md als HTML.
     */
    public function getInstallationMd(): string
    {
        $docFilePath = $this->getDocFilePath('install.md');
        if (!$docFilePath) {
            return '';
        }
        $path = $this->getUrlOrLocalRootPath() . $docFilePath;
        return FileHelper::readMarkdown($path);
    }

    /**
     * Liefert die usage.md als HTML.
     */
    public function getUsageMd(): string
    {
        $docFilePath = $this->getDocFilePath('usage.md');
        if (!$docFilePath) {
            return '';
        }
        $path = $this->getUrlOrLocalRootPath() . $docFilePath;
        return FileHelper::readMarkdown($path);
    }

    /**
     * Liefert die changelog.md als HTML.
     */
    public function getChangeLogMd(): string
    {
        $path = $this->getChangelogPath();
        if (!$path) {
            return '';
        }
        $path = $this->getUrlOrLocalRootPath() . $path;
        return FileHelper::readMarkdown($path);
    }

    /**
     * Liefert die README.md als HTML.
     */
    public function getReadmeMd(): string
    {
        $path = $this->getReadmePath();
        if (!$path) {
            return '';
        }
        $path = $this->getUrlOrLocalRootPath() . $path;
        return FileHelper::readMarkdown($path);
    }

    /**
     * Liefert den absoluten Pfad zur modulehash.json
     *
     * Beispiel:
     * /root/.../ModifiedModuleLoaderClient/{VENDOR-NAME}/{MODULE-NAME}/modulehash.json
     */
    public function getHashPath(): string
    {
        return App::getRoot() . $this->getModulePath() . '/modulehash.json';
    }

    public function isInstalled(): bool
    {
        if (file_exists($this->getHashPath())) {
            return true;
        }
        return false;
    }

    public function isChanged(): bool
    {
        //if ($this->getChancedFiles()) {
        if ($this->getChancedFilesNew()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Liefert true zurück, wenn das Modul geladen ist.
     * Wenn es sich um ein Remote Modul handelt, wird geschaut,
     * ob es das das gleiche Modul (arichveName:Version) auch lokal
     * gibt, ist das der Fall wird auch true zurück gegebn.
     * Ist das nicht der Fall wird false geliefert.
     */
    public function isLoaded(): bool
    {
        if (!$this->isRemote()) {
            return true;
        }

        $localModuleLoader = LocalModuleLoader::getModuleLoader();
        $localModule = $localModuleLoader->loadByArchiveNameAndVersion(
            $this->getArchiveName(),
            $this->getVersion()
        );

        if ($localModule) {
            return true;
        }

        return false;
    }

    /**
     * Checks whether this module is compatible with the installed version of modified.
     *
     * @return bool Returns true if the module is compatible, otherwise false.
     */
    public function isCompatible(): bool
    {
        $installedVersion = ShopInfo::getModifiedVersion();
        $versions = $this->getModifiedCompatibility();

        foreach ($versions as $version) {
            if ($installedVersion == $version) {
                return true;
            }
        }

        return false;
    }

    public function getTemplateFiles($file): array
    {
        $files = [];
        if (FileInfo::isTemplateFile($file)) {
            $templates = ShopInfo::getTemplates();
            foreach ($templates as $template) {
                $files[] = str_replace('/templates/tpl_modified/', '/templates/' . $template . '/', $file);
            }
        } else {
            $files[] = $file;
        }
        return $files;
    }

    /**
     * Returns a localy installed version of this module.
     *
     * @return Module|null Returns a localy installed version of this module or null.
     */
    public function getInstalledVersion(): ?Module
    {
        $modules = $this->getLocalVersions();
        $modules = ModuleFilter::filterInstalled($modules);
        return $modules[0] ?? null;
    }

    /**
     * Returns the latest version of this module.
     *
     */
    public function getNewestVersion(): Module
    {
        $moduleLoader = ModuleLoader::getModuleLoader();
        $modules = $moduleLoader->loadAllVersionsByArchiveNameWithLatestRemote($this->getArchiveName());
        if ($module = ModuleFilter::getLatestVersion($modules)) {
            return $module;
        }
        return $this;
    }

    /**
     * Retruns a array of modules.
     *
     * @return Module[] Returns a array of modules.
     */
    public function getVersions(): array
    {
        $moduleLoader = ModuleLoader::getModuleLoader();
        $modules = $moduleLoader->loadAllVersionsByArchiveName($this->getArchiveName());
        $modules = ModuleSorter::sortByVersion($modules);
        return $modules;
    }

    /**
     * Retruns a array of local modules.
     *
     * @return Module[] Returns a array of local modules.
     */
    public function getLocalVersions(): array
    {
        $localModuleLoader = LocalModuleLoader::getModuleLoader();
        $modules = $localModuleLoader->loadAllVersionsByArchiveName($this->getArchiveName());
        $modules = ModuleSorter::sortByVersion($modules);
        return $modules;
    }

    /**
     * HIER FEHLT EINE BESCHREIBUNG
     *
     * @return Module[]
     */
    public function getUsedBy(): array
    {
        $localModuleLoader = LocalModuleLoader::getModuleLoader();
        $installedModules = $localModuleLoader->loadAllInstalledVersions();

        $dependencyManager = new DependencyManager();
        $usedByEntrys = $dependencyManager->getUsedByEntrys($this, $installedModules);

        $usedByModules = [];
        foreach ($usedByEntrys as $usedByEntry) {
            $usedByModules[] = $usedByEntry['module'];
        }
        return $usedByModules;
    }

    /**
     * HIER FEHLT EINE BESCHREIBUNG
     *
     * @return string[]
     */
    public function getChancedFiles(): array
    {
        $moduleHasher = new ModuleHasher();
        $changedFiles = $moduleHasher->getModuleChanges($this);
        return $changedFiles;
    }

    /**
     * HIER FEHLT EINE BESCHREIBUNG
     *
     * @return string[]
     */
    public function getChancedFilesNew(): ChangedEntryCollection
    {
        $hashFileLoader = new HashFileLoader();
        $hashFile = $hashFileLoader->load(
            $this->getHashPath(),
            \RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\ModuleHasher::SCOPE_MODULE_SRC
        );

        $srcChangedEntiresCollection = $this->getSrcChanges($this, $hashFile);
        $srcMmlcChangedEntiresCollection = $this->getSrcMmlcChanges($this, $hashFile);

        $changedEntiresCollection = ChangedEntryCollection::merge([
            $srcChangedEntiresCollection,
            $srcMmlcChangedEntiresCollection
        ]);

        if ($this->getArchiveName() === 'robinthehood/modified-std-module') {
            echo '<pre>';
            print_r($changedEntiresCollection);
            die();
        }
        return $changedEntiresCollection;
    }

    private function getSrcChanges(Module $module, HashFile $hashFile): ChangedEntryCollection
    {
        $moduleHasher = new \RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\ModuleHasher(
            new \RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\FileHasher()
        );

        $installedHashes = $hashFile->getScopeHashes(
            \RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\ModuleHasher::SCOPE_MODULE_SRC
        );
        $shopHashes = $moduleHasher->createShopRootHashes($module);
        $srcHashes = $moduleHasher->createModuleSrcHashes($module);

        $comparator = new Comparator();
        $changedEntiresCollection = $comparator->getChangedEntries($installedHashes, $shopHashes, $srcHashes);

        return $changedEntiresCollection;
    }

    private function getSrcMmlcChanges(Module $module, HashFile $hashFile): ChangedEntryCollection
    {
        $moduleHasher = new \RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\ModuleHasher(
            new \RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\FileHasher()
        );

        $installedHashes = $hashFile->getScopeHashes(
            \RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher\ModuleHasher::SCOPE_MODULE_SRC_MMLC
        );
        $vendorMmlcHashes = $moduleHasher->createShopVendorMmlcHashes($module);
        $srcMmlcHashes = $moduleHasher->createModuleSrcMmlcHashes($module);

        $comparator = new Comparator();
        $changedEntiresCollection = $comparator->getChangedEntries($installedHashes, $vendorMmlcHashes, $srcMmlcHashes);

        return $changedEntiresCollection;
    }
}
