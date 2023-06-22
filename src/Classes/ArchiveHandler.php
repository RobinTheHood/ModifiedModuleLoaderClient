<?php

declare(strict_types=1);

namespace RobinTheHood\ModifiedModuleLoaderClient;

use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;

class ArchiveHandler
{
    private LocalModuleLoader $localModuleLoader;

    private string $modulesRootPath;

    public function __construct(LocalModuleLoader $localModuleLoader, string $modulesRootPath)
    {
        $this->localModuleLoader = $localModuleLoader;
        $this->modulesRootPath = $modulesRootPath;
    }

    public function pack(ArchiveNew $archive): void
    {
        $module = $this->localModuleLoader->loadByArchiveNameAndVersion(
            (string) $archive->getArchiveName(),
            (string) $archive->getVersion()
        );

        if (!$module) {
            throw new \RuntimeException("Failed to load module for archive: " . $archive->getArchiveName());
        }

        $this->createDirIfNotExists($archive->getArchivesRootPath());
        $this->deleteFileIfExists($archive->getFilePath());

        $filePaths = FileHelper::scanDirRecursive(
            $this->getModulePathFromModule($module),
            FileHelper::FILES_ONLY
        );

        set_time_limit(60 * 10);
        $tarArchive = new \PharData($archive->getFilePath());
        foreach ($filePaths as $filePath) {
            if (file_exists($filePath)) {
                $tarPath = FileHelper::stripBasePath($this->modulesRootPath, $filePath);
                $tarArchive->addFile($filePath, $tarPath);
            }
        }
    }

    public function extract(ArchiveNew $archive, bool $external = false): void
    {
        $modulePath = $this->getModulePathFromArchive($archive);
        if (file_exists($modulePath)) {
            throw new \RuntimeException("Module already exists for archive: " . $archive->getArchiveName());
        }

        $this->createDirIfNotExists($this->modulesRootPath);

        $tarArchive = new \PharData($archive->getFilePath());
        $tarArchive->extractTo($this->modulesRootPath);

        if ($external) {
            $vendorDirPath = $this->modulesRootPath . '/' . $archive->getArchiveName()->getVendorName();
            $moduleDirPath = $this->modulesRootPath . '/' . $archive->getArchiveName();

            $this->createDirIfNotExists($vendorDirPath);
            $this->createDirIfNotExists($moduleDirPath);

            rename(
                $this->modulesRootPath . '/' . $tarArchive->getFileName(),
                $this->getModulePathFromArchive($archive)
            );
        }
    }

    private function getModulePathFromArchive(ArchiveNew $archiveNew): string
    {
        return $this->modulesRootPath . '/' . $archiveNew->getArchiveName() . '/' . $archiveNew->getVersion();
    }

    private function getModulePathFromModule(Module $module): string
    {
        return $module->getLocalRootPath() . DIRECTORY_SEPARATOR . $module->getModulePath();
    }

    private function createDirIfNotExists(string $path): void
    {
        if (!@mkdir($path) && !is_dir($path)) {
            throw new \RuntimeException("Failed to create directory: " . $path);
        }
    }

    private function deleteFileIfExists(string $path): void
    {
        if (!@unlink($path) && file_exists($path)) {
            throw new \RuntimeException("Failed to delete file: " . $path);
        }
    }
}
