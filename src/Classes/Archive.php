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
use RobinTheHood\ModifiedModuleLoaderClient\Api\HttpRequest;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\ServerHelper;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;

class Archive
{
    private $localRootPath;
    private $urlRootPath;
    private $archiveName;
    private $version;

    public function __construct(string $archiveName, string $version)
    {
        $this->localRootPath = App::getArchivesRoot();
        $this->urlRootPath = ServerHelper::getUri() . '/Archives';
        $this->archiveName = $archiveName;
        $this->version = $version;
    }

    public function getLocalRootPath(): string
    {
        return $this->localRootPath;
    }

    public function getUrlRootPath(): string
    {
        return $this->urlRootPath;
    }

    public function getArchiveName(): string
    {
        return $this->archiveName;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getUrl(): string
    {
        return $this->getUrlRootPath() . '/' . $this->getFileArchvieName();
    }

    public function getPath(): string
    {
        return $this->getLocalRootPath() . '/' . $this->getFileArchvieName();
    }

    public function getFileArchvieName(): string
    {
        return str_replace('/', '_', $this->getArchiveName()) . '_' . $this->getVersion() . '.tar';
    }

    public function getModulePath(): string
    {
        return App::getModulesRoot() . '/' . $this->getArchiveName() . '/' . $this->getVersion();
    }

    public function getVendorName(): string
    {
        $parts = explode('/', $this->getArchiveName());
        $vendorName = $parts[0];
        return $vendorName;
    }

    public function tarArchive(): bool
    {
        $localModuleLoader = new LocalModuleLoader();
        $module = $localModuleLoader->loadByArchiveNameAndVersion($this->getArchiveName(), $this->getVersion());

        if (!$module) {
            return false;
        }

        $src = $module->getLocalRootPath() . $module->getModulePath();
        $dest = $this->getPath();

        if (file_exists($dest)) {
            //return true;
        }

        @mkdir($this->getLocalRootPath());
        @unlink($dest);

        $filePaths = FileHelper::scanDirRecursive($src, FileHelper::FILES_ONLY);

        set_time_limit(60 * 10);
        $tarBall = new \PharData($dest);
        foreach ($filePaths as $filePath) {
            if (file_exists($filePath)) {
                $tarPath = FileHelper::stripBasePath(App::getModulesRoot(), $filePath);
                $tarBall->addFile($filePath, $tarPath);
            }
        }

        return true;
    }

    public function untarArchive(bool $external = false): bool
    {
        @mkdir(App::getModulesRoot());

        $tarBall = new \PharData($this->getPath());
        if (file_exists(App::getModulesRoot() . '/' . $this->getArchiveName() . '/' . $this->getVersion())) {
            return false;
        }
        $tarBall->extractTo(App::getModulesRoot());

        // Wenn die Tar Datei z.B. von Github kommt, ist die Ordnerstrukur nicht
        // 100%tig kompatibel. In diesem Fall müssen Vendor/ModuleName/Version
        // noch angelegt werden.
        if ($external) {
            $fileName = $tarBall->getFileName();
            @mkdir(App::getModulesRoot() . '/' . $this->getVendorName());
            @mkdir(App::getModulesRoot() . '/' . $this->getArchiveName());
            rename(App::getModulesRoot() . '/' . $fileName, $this->getModulePath());
        }

        return true;
    }

    public static function pullArchive(string $path, string $archiveName, string $version): Archive
    {
        $archive = new Archive($archiveName, $version);

        $httpRequest = new HttpRequest();
        $tarBall = $httpRequest->sendGetRequest($path);

        // TODO - check if $tarBall is a tarball or a error response form request
        
        if (!$tarBall) {
            return false;
        }

        @mkdir($archive->getLocalRootPath());
        file_put_contents($archive->getPath(), $tarBall);

        return $archive;
    }
}
