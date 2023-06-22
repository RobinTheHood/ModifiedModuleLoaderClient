<?php

declare(strict_types=1);

namespace RobinTheHood\ModifiedModuleLoaderClient;

use RobinTheHood\ModifiedModuleLoaderClient\Api\V1\HttpRequest;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;
use RuntimeException;

class ArchivePuller
{
    private HttpRequest $httpRequest;
    private Parser $parser;
    private string $archivesRootPath;

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

    public function pull(string $archiveName, string $version, string $url): ArchiveNew
    {
        $archive = $this->createArchive($archiveName, $version);

        $tarArchiveContent = $this->httpRequest->sendGetRequest($url);

        if (!$tarArchiveContent || !$this->isTarArchive($tarArchiveContent)) {
            throw new RuntimeException("Failed to pull Archive: {$archiveName}:{$version}");
        }

        $this->createDirIfNotExists($archive->getArchivesRootPath());
        file_put_contents($archive->getFilePath(), $tarArchiveContent);

        return $archive;
    }

    private function createArchive(string $archiveName, string $version): ArchiveNew
    {
        $archiveNameObj = new ArchiveName($archiveName);
        $versionObj = $this->parser->parse($version);

        return new ArchiveNew($archiveNameObj, $versionObj, $this->archivesRootPath);
    }

    private function isTarArchive($content): bool
    {
        // Überprüfen, ob $content mit der Tarball-Signatur beginnt
        $tarballSignature = "\x1f\x8b\x08";
        $firstBytes = substr($content, 0, 3);

        return ($firstBytes === $tarballSignature);
    }

    private function createDirIfNotExists(string $path): void
    {
        if (!@mkdir($path) && !is_dir($path)) {
            throw new \RuntimeException("Failed to create directory: " . $path);
        }
    }
}
