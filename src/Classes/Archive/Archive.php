<?php

declare(strict_types=1);

namespace RobinTheHood\ModifiedModuleLoaderClient\Archive;

use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Version;

/**
 * Diese Klasse repräsentiert ein Modul gepackt als .tar Datei. Die .tar Datei befindet sich im Datei System und nicht
 * als Data-String einem Objekt dieser Klasse. Die Methode getFilePath() liefert die Pfad zu .tar Datei.
 */
class Archive
{
    /** @var ArchiveName */
    private $archiveName;

    /** @var Version */
    private $version;

    /** @var string */
    private $archivesRootPath;

    public static function create(string $archiveName, string $version, string $archivesRootPath): Archive
    {
        $archiveNameObj = new ArchiveName($archiveName);
        $semverParser = Parser::create();
        $versionObj = $semverParser->parse($version);

        return new Archive($archiveNameObj, $versionObj, $archivesRootPath);
    }

    public function __construct(
        ArchiveName $archiveName,
        Version $version,
        string $archivesRootPath
    ) {
        if (empty($archivesRootPath)) {
            throw new \InvalidArgumentException('archivesRootPath cannot be empty.');
        }

        $this->archiveName = $archiveName;
        $this->archivesRootPath = $archivesRootPath;
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

    /**
     * Liefert den Root Path zum Archive Ordner
     * z. B. /.../ModifiedModuleLoaderClient/Archives/
     */
    public function getArchivesRootPath(): string
    {
        return $this->archivesRootPath;
    }

    /**
     * Liefert den Dateinamen der .tar Datei
     * z. B. robinthehood_modified-std-module_0.1.0.tar
     */
    public function getFileName(): string
    {
        return
            str_replace('/', '_', $this->getArchiveName()->__toString())
            . '_'
            . $this->getVersion()
            . '.tar';
    }

    /**
     * Liefert den gesamten Path der .tar Datei.
     * z. B. /.../ModifiedModuleLoaderClient/Archives/robinthehood_modified-std-module_0.1.0.tar
     */
    public function getFilePath(): string
    {
        return $this->getArchivesRootPath() . '/' . $this->getFileName();
    }
}
