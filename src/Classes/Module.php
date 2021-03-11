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

use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\ShopInfo;
use RobinTheHood\ModifiedModuleLoaderClient\FileInfo;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFiler;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleInfo;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager;
use RobinTheHood\ModifiedModuleLoaderClient\Api\ApiRequest;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\ModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\ArrayHelper;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\ServerHelper;

class Module extends ModuleInfo
{
    private $localRootPath;
    private $urlRootPath;
    private $modulePath;
    private $iconPath;
    private $imagePaths;
    private $docFilePaths;
    private $changelogPath;
    private $readmePath;
    private $srcFilePaths;
    private $isRemote;
    private $isLoadable;

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

    // ...shop.de
    public function getUrlRootPath(): string
    {
        return $this->urlRootPath;
    }

    public function setUrlRootPath(string $value): void
    {
        $this->urlRootPath = $value;
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

    // /Modules/{VENDOR-NAME}/{MODULE-NAME}/new_files
    public function getSrcRootPath(): string
    {
        return $this->getModulePath() . '/' . $this->getSourceDir();
    }

    // ...shop.de/Modules/{VENDOR-NAME}/{MODULE-NAME}/icon.xxx
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
            return;
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
            return;
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
            return;
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
            return;
        }
        $path = $this->getUrlOrLocalRootPath() . $path;
        return FileHelper::readMarkdown($path);
    }

    public function getHashFileName()
    {
        return 'modulehash.json';
    }

    /**
     * Liefert den absoluten Pfad zur modulehash.json
     *
     * Beispiel:
     * /root/.../ModifiedModuleLoaderClient/{VENDOR-NAME}/{MODULE-NAME}/modulehash.json
     */
    public function getHashPath(): string
    {
        return App::getRoot() . $this->getModulePath() . '/' . $this->getHashFileName();
    }

    public function setRemote(bool $value): void
    {
        $this->isRemote = $value;
    }

    /**
     * Liefert true, wenn es sich um ein Remote Modul handelt.
     */
    public function isRemote(): bool
    {
        return $this->isRemote;
    }

    /**
     * Liefert true, wenn das Module geladen werden darf/kann.
     */
    public function setLoadable($value)
    {
        $this->isLoadable = $value;
    }

    public function isLoadable()
    {
        return $this->isLoadable;
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
        if ($this->getChancedFiles()) {
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
        if ($this->isRemote()) {
            $localModuleLoader = LocalModuleLoader::getModuleLoader();
            $localModules = $localModuleLoader->loadAllVersions();

            foreach ($localModules as $module) {
                if ($module->getArchiveName() != $this->getArchiveName()) {
                    continue;
                }

                if ($module->getVersion() != $this->getVersion()) {
                    continue;
                }
                return true;
            }
            return false;
        }
        return true;
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

    public function load($path)
    {
        $result = $this->loadFromJson($path . '/moduleinfo.json');

        if (!$result) {
            return false;
        }

        $this->localRootPath = App::getRoot();
        $this->urlRootPath = ServerHelper::getUri();
        $this->modulePath = FileHelper::stripBasePath(App::getRoot(), $path);

        $this->iconPath = $this->loadIconPath($path);
        $this->imagePaths = $this->loadImagePaths($path . '/images');
        $this->docFilePaths = $this->loadDocFilePaths($path . '/docs');
        $this->changelogPath = $this->loadChangelogPath($path);
        $this->readmePath = $this->loadReadmePath($path);
        $this->srcFilePaths = $this->loadSrcFilePaths($this->getLocalRootPath() . $this->getSrcRootPath());

        return true;
    }

    public function loadIconPath($path)
    {
        if (file_exists($path . '/icon.jpg')) {
            $iconPath = $this->getModulePath() . '/icon.jpg';
        } elseif (file_exists($path . '/icon.png')) {
            $iconPath = $this->getModulePath() . '/icon.png';
        } else {
            if ($this->getCategory() == 'library') {
                $iconPath = '/src/Templates/Images/icon_library.png';
            } else {
                $iconPath = '/src/Templates/Images/icon_module.png';
            }
        }

        return $iconPath;
    }

    public function loadImagePaths($path)
    {
        if (!is_dir($path)) {
            return [];
        }

        $images = [];

        $fileNames = scandir($path);
        foreach ($fileNames as $fileName) {
            if (strpos($fileName, '.jpg') || strpos($fileName, '.png')) {
                $images[] = $path . '/' . $fileName;
            }
        }

        $images = FileHelper::stripAllBasePaths(App::getRoot(), $images);

        return $images;
    }

    public function loadDocFilePaths($path)
    {
        if (!is_dir($path)) {
            return [];
        }

        $docFiles = [];

        $fileNames = scandir($path);
        foreach ($fileNames as $fileName) {
            if (strpos($fileName, '.md')) {
                $docFiles[] = $path . '/' . $fileName;
            }
        }

        $docFiles = FileHelper::stripAllBasePaths(App::getRoot(), $docFiles);

        return $docFiles;
    }

    public function loadChangeLogPath($path)
    {
        $changelogPath = '';

        if (file_exists($path . '/changelog.md')) {
            $changelogPath = $this->getModulePath() . '/changelog.md';
        }

        return $changelogPath;
    }

    public function loadReadmePath($path)
    {
        $readmePath = '';

        if (file_exists($path . '/README.md')) {
            $readmePath = $this->getModulePath() . '/README.md';
        } elseif (file_exists($path . '/readme.md')) {
            $readmePath = $this->getModulePath() . '/readme.md';
        }

        return $readmePath;
    }

    public function loadSrcFilePaths($path)
    {
        $filePaths = FileHelper::scanDirRecursive($path, FileHelper::FILES_ONLY, true);
        $filePaths = FileHelper::stripAllBasePaths($path, $filePaths);
        return $filePaths;
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
        return ArrayHelper::getIfSet($modules, 0, null);
    }

    /**
     * Returns the latest version of this module.
     *
     */
    public function getNewestVersion(): Module
    {
        $moduleLoader = ModuleLoader::getModuleLoader();
        $modules = $moduleLoader->loadAllVersionsByArchiveNameWithLatestRemote($this->getArchiveName());
        $module = ModuleFilter::getLatestVersion($modules);
        return $module;
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
        $dependencyManager = new DependencyManager();
        $installedModules = $dependencyManager->getInstalledModules();
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

    public function loadFromArray(array $array)
    {
        parent::loadFromArray($array);

        $this->localRootPath = ArrayHelper::getIfSet($array, 'localRootPath');
        $this->urlRootPath = ArrayHelper::getIfSet($array, 'urlRootPath');
        $this->modulePath = ArrayHelper::getIfSet($array, 'modulePath');
        $this->iconPath = ArrayHelper::getIfSet($array, 'iconPath');
        $this->imagePaths = ArrayHelper::getIfSet($array, 'imagePaths', []);
        $this->docFilePaths = ArrayHelper::getIfSet($array, 'docFilePaths', []);
        $this->changelogPath = ArrayHelper::getIfSet($array, 'changelogPath');
        $this->readmePath = ArrayHelper::getIfSet($array, 'readmePath');
        $this->srcFilePaths = ArrayHelper::getIfSet($array, 'rootPath');
        $this->isRemote = ArrayHelper::getIfSet($array, 'isRemote');
        $this->isLoadable = ArrayHelper::getIfSet($array, 'isLoadable');

        return true;
    }

    public function toArray()
    {
        $moduleInfoArray = parent::toArray();

        $moduleArray = [
            'localRootPath' => $this->getLocalRootPath(),
            'urlRootPath' => $this->getUrlRootPath(),
            'modulePath' => $this->getModulePath(),
            'iconPath' => $this->getIconPath(),
            'imagePaths' => $this->getImagePaths(),
            'docFilePaths' => $this->getDocFilePaths(),
            'changelogPath' => $this->getChangelogPath(),
            'readmePath' => $this->getReadmePath(),
            'srcFilePaths' => $this->getSrcFilePaths(),
            'isRemote' => $this->isRemote(),
            'isLoadable' => $this->isLoadable()
        ];

        return array_merge($moduleInfoArray, $moduleArray);
    }

    public function getPriceFormated(): string
    {
        if ($this->getPrice() === 'free') {
            return '<span class="price-free">kostenlos</span>';
        } elseif (!$this->getPrice()) {
            return '<span class="price-request">Preis auf Anfrage</span>';
        } else {
            return '<span class="price-normal">' . number_format((float) $this->getPrice(), 2, ',', '.') . ' € </span>';
        }
    }
}
