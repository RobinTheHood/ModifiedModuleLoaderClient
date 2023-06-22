<?php

declare(strict_types=1);

namespace RobinTheHood\ModifiedModuleLoaderClient;

use RobinTheHood\ModifiedModuleLoaderClient\Semver\Version;

class ArchiveNew
{
    private ArchiveName $archiveName;
    private Version $version;
    private string $archivesRootPath;
    // private string $urlRootPath;

    public function __construct(
        ArchiveName $archiveName,
        Version $version,
        string $archivesRootPath
        // string $urlRootPath,
    ) {
        if (empty($archivesRootPath)) {
            throw new \InvalidArgumentException('archivesRootPath cannot be empty.');
        }

        // if (empty($urlRootPath)) {
        //     throw new \InvalidArgumentException('urlRootPath cannot be empty.');
        // }

        $this->archiveName = $archiveName;
        $this->archivesRootPath = $archivesRootPath;
        // $this->urlRootPath = $urlRootPath;
        $this->version = $version;
    }

    public function getArchiveName(): ArchiveName
    {
        return $this->archiveName;
    }

    public function getVersion(): Version
    {
        return $this->version;
    }

    public function getArchivesRootPath(): string
    {
        return $this->archivesRootPath;
    }

    // public function getUrlRootPath(): string
    // {
    //     return $this->urlRootPath;
    // }

    public function getFileName(): string
    {
        return
            str_replace('/', '_', $this->getArchiveName()->__toString())
            . '_'
            . $this->getVersion()
            . '.tar';
    }

    public function getFilePath(): string
    {
        return $this->getArchivesRootPath() . '/' . $this->getFileName();
    }
}
