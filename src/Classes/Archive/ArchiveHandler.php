<?php

declare(strict_types=1);

namespace RobinTheHood\ModifiedModuleLoaderClient\Archive;

use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Module;

/**
 * Diese Klasse ist für das Packen und Entpacken von Archiven zuständig
 */
class ArchiveHandler
{
    /** @var LocalModuleLoader */
    private $localModuleLoader;

    /** @var string /.../ModifiedModuleLoaderClient/Modules */
    private $modulesRootPath;

    public static function create(int $mode): ArchiveHandler
    {
        $localModuleLoader = LocalModuleLoader::create($mode);
        $archiveHandler = new ArchiveHandler(
            $localModuleLoader,
            App::getModulesRoot()
        );
        return $archiveHandler;
    }

    public function __construct(LocalModuleLoader $localModuleLoader, string $modulesRootPath)
    {
        $this->localModuleLoader = $localModuleLoader;
        $this->modulesRootPath = $modulesRootPath;
    }

    /**
     * Packt ein Archive zu einer .tar Datei.
     *
     * Dabei ist in der .tar Datei die Verzeichnis-Struktur <vendor-name>/<module-name>/<version> enthalten
     *
     * @throws \RuntimeException if an error occurs
     */
    public function pack(Archive $archive): void
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

    /**
     * Entpack ein $archive.
     *
     * Dabei wird das Archive in Modules entpackt. Im .tar archive selbst liegen die Dateien in der Ordner-Strucktur
     * /<vendor-name>/<module-name>/<version> vor.
     *
     * @param Archive $archive
     * @param bool $external Wenn eine .tar Datei z. B. von Github kommt, ist die Ordner-Strucktur in der .tar Datei
     * nicht kompatible. Im der .tar Datei fehlt das Verzeichnis /<vendor-name>/<module-name>/<version>. Es ist nur
     * das Verzeichnis <version> vorhandnen. In diesem Fall muss das Verzeichnis /<vendor-name>/<module-name>/
     * angelegt werden.
     *
     * @throws \RuntimeException if an error occurs
     */
    public function extract(Archive $archive, bool $external = false): void
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

    /**
     * Liefert zu einem Archive den ModulePath
     * z. B. /.../ModifiedModuleLoaderClient/Modules/composer/autoload/1.0.0/
     */
    private function getModulePathFromArchive(Archive $archive): string
    {
        return $this->modulesRootPath . '/' . $archive->getArchiveName() . '/' . $archive->getVersion();
    }

    /**
     * Liefert den gesatem Modul Path zu einem Modul
     * z. B. /.../ModifiedModuleLoaderClient/Modules/composer/autoload/
     */
    private function getModulePathFromModule(Module $module): string
    {
        return $module->getLocalRootPath() . $module->getModulePath();
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

    /**
     * // TODO: Man könnte diese Methode in die Klasse FileHelper auslagern
     */
    private function deleteFileIfExists(string $path): void
    {
        if (!@unlink($path) && file_exists($path)) {
            throw new \RuntimeException("Failed to delete file: " . $path);
        }
    }
}
