<?php

declare(strict_types=1);

namespace RobinTheHood\ModifiedModuleLoaderClient\Archive;

use RobinTheHood\ModifiedModuleLoaderClient\Api\V1\HttpRequest;
use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;
use RuntimeException;

/**
 * Die Klasse ist für das Herunterladen von Archiven vom Server zuständig. Ein Archive repräsentiert ein Modul
 * gepackt als .tar Datei.
 */
class ArchivePuller
{
    /** @var HttpRequest */
    private $httpRequest;

    /** @var Parser */
    private $parser;

    /** @var string */
    private $archivesRootPath;

    public static function create(): ArchivePuller
    {
        $httpRequest = new HttpRequest();
        $parser = Parser::create();
        $archivePuller = new ArchivePuller($httpRequest, $parser, App::getArchivesRoot());
        return $archivePuller;
    }

    public function __construct(HttpRequest $httpRequest, Parser $parser, string $archivesRootPath)
    {
        $this->httpRequest = $httpRequest;
        $this->parser = $parser;
        $this->archivesRootPath = $archivesRootPath;
    }

    /**
     * @throws RuntimeException wenn das Archive nicht vom Server geladen werden konnte.
     */
    public function pull(string $archiveName, string $version, string $url): Archive
    {
        $archive = $this->createArchiveObjFromStrings($archiveName, $version);

        $tarArchiveContent = $this->httpRequest->sendGetRequest($url);

        if (!$tarArchiveContent || !$this->isTarArchive($tarArchiveContent)) {
            throw new RuntimeException("Failed to pull Archive: {$archiveName}:{$version}");
        }

        $this->createDirIfNotExists($archive->getArchivesRootPath());
        file_put_contents($archive->getFilePath(), $tarArchiveContent);

        return $archive;
    }

    private function createArchiveObjFromStrings(string $archiveName, string $version): Archive
    {
        $archiveNameObj = new ArchiveName($archiveName);
        $versionObj = $this->parser->parse($version);

        return new Archive($archiveNameObj, $versionObj, $this->archivesRootPath);
    }

    /**
     * Überprüft, ob $content die Tarball-Signatur beinhaltet
     *
     * @param string $content .tar File as string
     *
     * // TODO: Man könnte diese Methode als eigenständige Klasse TarFileVerifier auslagern
     */
    private function isTarArchive(string $content): bool
    {
        $tarballSignature = "ustar";
        $magic = substr($content, 257, 5);

        return ($magic === $tarballSignature);
    }

    /**
     * // TODO: Man könnte diese Methode in die Klasse FileHelper auslagern
     */
    private function createDirIfNotExists(string $path): void
    {
        if (!@mkdir($path) && !is_dir($path)) {
            throw new \RuntimeException("Failed to create directory: " . $path);
        }
    }
}
