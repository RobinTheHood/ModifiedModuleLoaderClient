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
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\DependencyManager;
use RobinTheHood\ModifiedModuleLoaderClient\ShopInfo;
use RobinTheHood\ModifiedModuleLoaderClient\FileInfo;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFilter;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleInfo;
use RobinTheHood\ModifiedModuleLoaderClient\FileHasher\ChangedEntryCollection;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\ModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;

class Module extends ModuleInfo
{
    /**
     * @var string
     */
    private $localRootPath;

    /**
     * @var string
     */
    private $urlRootPath;

    /**
     * @var string
     */
    private $modulePath;

    /**
     * @var string
     */
    private $iconPath;

    /**
     * @var string[]
     */
    private $imagePaths;

    /**
     * @var string[]
     */
    private $docFilePaths;

    /**
     * @var string
     */
    private $changelogPath;

    /**
     * @var string
     */
    private $readmePath;

    /**
     * @var string[]
     */
    private $srcFilePaths;

    /**
     * @var string[]
     */
    private $srcMmlcFilePaths;

    /**
     * @var bool
     */
    private $isRemote;

    /**
     * @var bool
     */
    private $isLoadable;

    /**
     * Liefert den absoluten Pfad zum MMLC-Root Verzeichnis.
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
     * Liefert ein Array mit Dateienpfaden, die sich in 'src'
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
     * Liefert ein Array mit Dateienpfaden, die sich in 'src-mmlc'
     * befinden.
     */
    public function getSrcMmlcFilePaths(): array
    {
        return $this->srcMmlcFilePaths;
    }

    public function setSrcMmlcFilePaths(array $value): void
    {
        $this->srcMmlcFilePaths = $value;
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
     * /Modules/{VENDOR-NAME}/{MODULE-NAME}/src
     */
    public function getSrcRootPath(): string
    {
        return $this->getModulePath() . '/' . $this->getSourceDir();
    }

    /**
     * HIER FEHLT EINE BESCHREIBUNG
     *
     * /Modules/{VENDOR-NAME}/{MODULE-NAME}/src-mmlc
     */
    public function getSrcMmlcRootPath(): string
    {
        return $this->getModulePath() . '/' . $this->getSourceMmlcDir();
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
     * Liefert die description.md als HTML.
     */
    public function getDescriptionMd(): string
    {
        $docFilePath = $this->getDocFilePath('description.md');
        if (!$docFilePath) {
            return '';
        }
        $path = $this->getUrlOrLocalRootPath() . $docFilePath;
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
        if ($this->getChancedFiles()->changedEntries) {
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

        $localModuleLoader = LocalModuleLoader::createFromConfig();
        $localModule = $localModuleLoader->loadByArchiveNameAndVersion(
            $this->getArchiveName(),
            $this->getVersion()
        );

        if ($localModule) {
            return true;
        }

        return false;
    }


    public function isCompatible(): bool
    {
        if (!$this->isCompatibleWithModified()) {
            return false;
        }

        if (!$this->isCompatibleWithPhp()) {
            return false;
        }

        if (!$this->isCompatibleWithMmlc()) {
            return false;
        }

        return true;
    }

    /**
     * Checks whether this module is compatible with the installed version of modified.
     *
     * @return bool Returns true if the module is compatible, otherwise false.
     */
    public function isCompatibleWithModified(): bool
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

    public function isCompatibleWithPhp(): bool
    {
        $php = $this->getPhp();
        $phpVersionContraint = $php['version'] ?? '';
        if (!$phpVersionContraint) {
            return true;
        }

        $phpVersionInstalled = phpversion();
        $comparator = SemverComparatorFactory::createComparator();
        return $comparator->satisfies($phpVersionInstalled, $phpVersionContraint);
    }

    public function isCompatibleWithMmlc(): bool
    {
        $mmlcVersionInstalled = App::getMmlcVersion();
        if (!$mmlcVersionInstalled) {
            return false;
        }

        $mmlc = $this->getMmlc();
        $mmlcVersionContraint = $mmlc['version'] ?? '';
        if (!$mmlcVersionContraint) {
            return true;
        }

        $comparator = SemverComparatorFactory::createComparator();
        return $comparator->satisfies($mmlcVersionInstalled, $mmlcVersionContraint);
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
        $moduleFilter = ModuleFilter::createFromConfig();
        $modules = $moduleFilter->filterInstalled($modules);
        return $modules[0] ?? null;
    }

    /**
     * Returns the latest version of this module.
     *
     */
    public function getNewestVersion(): Module
    {
        $moduleLoader = ModuleLoader::createFromConfig();
        $modules = $moduleLoader->loadAllVersionsByArchiveNameWithLatestRemote($this->getArchiveName());
        $moduleFilter = ModuleFilter::createFromConfig();
        if ($module = $moduleFilter->getLatestVersion($modules)) {
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
        $moduleLoader = ModuleLoader::createFromConfig();
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
        $localModuleLoader = LocalModuleLoader::createFromConfig();
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
        $localModuleLoader = LocalModuleLoader::createFromConfig();
        $installedModules = $localModuleLoader->loadAllInstalledVersions();

        // TODO: DI besser machen
        $dependencyManager = DependencyManager::createFromConfig();
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
     * @return ChangedEntryCollection
     */
    public function getChancedFiles(): ChangedEntryCollection
    {
        return ModuleChangeManager::getChangedFiles($this);
    }
}
