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

use RuntimeException;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\DependencyManager;
use RobinTheHood\ModifiedModuleLoaderClient\Logger\LogLevel;
use RobinTheHood\ModifiedModuleLoaderClient\Logger\StaticLogger;

class ModuleInstaller3
{
    /** @var DependencyManager */
    private $dependencyManager;

    /** @var UnsaveModuleInstaller */
    private $unsaveModuleInstaller;

    public static function create(int $mode): ModuleInstaller3
    {
        $dependencyManager = DependencyManager::create($mode);
        $unsaveModuleInstaller = UnsaveModuleInstaller::create($mode);
        $moduleInstaller = new ModuleInstaller3(
            $dependencyManager,
            $unsaveModuleInstaller
        );
        return $moduleInstaller;
    }

    public static function createFromConfig(): ModuleInstaller3
    {
        return self::create(Config::getDependenyMode());
    }

    public function __construct(
        DependencyManager $dependencyManager,
        UnsaveModuleInstaller $unsaveModuleInstaller
    ) {
        $this->dependencyManager = $dependencyManager;
        $this->unsaveModuleInstaller = $unsaveModuleInstaller;
    }

    /**
     * Lädt ein Modul (archiveName, Version) vom Server herunter.
     *
     * Mögliche Fehler und Exceptions
     * - [ ] Keine Verbindung zum Server - NoServerConnectionException extends PullException
     * - [ ] Nicht genügent Speicherplatz - InsufficientDiskSpaceException extends PullException
     * - [ ] Zip Datei kann nicht entpackt werden - UnzipFailedException extends PullException
     * - [ ] Keine Schreibrechte für Archives - NoWritePermissionForArchivesException extends PullException
     * - [ ] Keine Schreibrechte für Modules - NoWritePermissionForModulesException extends PullException
     * - [x] Das Modul ist bereits geladen - ModuleAlreadyLoadedException extends PullException
     */
    public function pull(Module $module): Module
    {
        $moduleText = "module {$module->getArchiveName()} {$module->getVersion()}";

        if ($module->isLoaded()) {
            throw new RuntimeException("Can not pull $moduleText. Modul is already loaded.");
        }

        return $this->unsaveModuleInstaller->pull($module);
    }

    /**
     * Löscht ein Modul (archiveName, Version) das bereits heruntergeladen wurde.
     *
     * Mögliche Fehler und Exceptions
     * - [ ] Es ist ein reines lokael Modul und kann deswegen nicht gelöscht werden
     * - [x] Das Modul ist nicht geladen und kann deswegen nicht gelöscht werden
     * - [x] Das Modul ist noch installiert und kann deswegen nicht gelöscht werden
     * - [ ] Keine Schreibrechte für Modules
     *
     * Fragen
     * - Was passiert, wenn nicht alle Datein gelöscht werden konnten, dann ist das Modul in einem defekten Zustand
     */
    public function delete(Module $module): void
    {
        $moduleText = "module {$module->getArchiveName()} {$module->getVersion()}";

        if (!$module->isLoaded()) {
            throw new RuntimeException("Can not delete $moduleText. Module is not loaded.");
        }

        if ($module->isInstalled()) {
            throw new RuntimeException("Can not delete $moduleText. Module is installed.");
        }

        $this->unsaveModuleInstaller->delete($module);
    }

    /**
     * Installiert ein Modul (archiveName, Version) in das Shop System UND lädt und installiert alle Abhängigkeiten /
     * abhängige Module nach.
     *
     * Mögliche Fehler und Exceptions
     * - alle Fehler aus internalInstall()
     * - alle Fehler aus internalInstallDependencies()
     * - Keine passende kombination an Abhänigkeiten gefunden
     * - Autoload kann nicht aktuallisiert werden
     * - Das Modull ist bereits installiert
     */
    public function install(Module $module): void
    {
        $moduleText = "module {$module->getArchiveName()} {$module->getVersion()}";

        if ($module->isInstalled()) {
            throw new RuntimeException("Can not install $moduleText. Module is already installed.");
        }

        $this->unsaveModuleInstaller->install($module);

        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();
    }

    /**
     * Installiert ein Modul (archiveName, Version) in das Shop System ABER lädt und installiert KEINE Abhängigkeiten /
     * abhängige Module nach. Sind nicht alle Abhängigkeiten erfüllt, wird nicht installiert und eine Exception
     * geworfen.
     *
     * @param bool $skipDependencyCheck skip dependency check.
     */
    public function installWithoutDependencies(Module $module, bool $skipDependencyCheck = false): void
    {
        $moduleText = "module {$module->getArchiveName()} {$module->getVersion()}";

        if ($module->isInstalled()) {
            throw new RuntimeException("Can not install $moduleText. Module is already installed.");
        }

        if (!$skipDependencyCheck) {
            // Wirft eine Exception, wenn keine passenen Kombination gefunden wurde.
            $combinationSatisfyerResult = $this->dependencyManager->canBeInstalled($module);

            if (!$combinationSatisfyerResult->foundCombination) {
                $message = "Can not update $moduleText. No possible combination of versions found";
                StaticLogger::log(LogLevel::WARNING, $message);
                // NOTE: Vielleicht neue class ModuleException hinzufügen
                throw new RuntimeException($message);
            }
        }

        $this->unsaveModuleInstaller->installWithoutDependencies($module);

        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();
    }

    /**
     * //TODO: Nicht zur Neusten sondern zu höchst möglichsten Version aktualisieren.
     *
     * Aktualiseirt NUR das Modul (vendorName) auf die neuste Version. Es werden keine fehlenden Abhänggigkeiten
     * installiert. Es werden keine Abhänggigkeiten aktualisiert. Können nicht alle Abhängigkeiten erfüllt werten,
     * wird nicht aktualisiert und eine Exception geworfen.
     */
    public function updateWithoutMissingDependencies(Module $module, bool $skipDependencyCheck = false): Module
    {
        $moduleText = "module {$module->getArchiveName()} {$module->getVersion()}";

        if (!$module->isInstalled()) {
            throw new RuntimeException("Can not update $moduleText. Module is not installed.");
        }

        $loadedNewModul = $this->unsaveModuleInstaller->updateWithoutMissingDependencies($module, $skipDependencyCheck);

        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();

        return $loadedNewModul;
    }

    /**
     * //TODO: Nicht zur Neusten sondern zu höchst möglichsten Version aktualisieren.
     *
     * Aktuallisiert das Modul (vendorName) auf die neuste Version. Dabei werden keine Abhänggigkeiten
     * aktualisiert. Kommen durch das Update jedoch neue Abhänigkeiten hinzu, werden diese installt. Können nicht alle
     * Abhängigkeiten erfüllt werten, wird nicht aktualisiert und eine Exception geworfen.
     */
    public function update(Module $module): Module
    {
        $moduleText = "module {$module->getArchiveName()} {$module->getVersion()}";

        if (!$module->isInstalled()) {
            throw new RuntimeException("Can not update $moduleText. Module is not installed.");
        }

        $newModule = $this->unsaveModuleInstaller->update($module);

        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();

        return $newModule;
    }

    /**
     * Entfernt alle Änderungen die an den Modul-Dateien im Shop gemacht wurden. Änderungen an Template Dateien werden
     * nicht rückgängig gemacht.
     *
     * //TODO: bool $force Parameter einführen der auch Änderungen am Template entfernt.
     */
    public function discard(Module $module): void
    {
        $moduleText = "module {$module->getArchiveName()} {$module->getVersion()}";

        if (!$module->isInstalled()) {
            $message = "Can not revert changes because $moduleText is not installed.";
            StaticLogger::log(LogLevel::WARNING, $message);
            // NOTE: Vielleicht neue class ModuleException hinzufügen
            throw new RuntimeException($message);
        }

        $moduleFileInstaller = new ModuleFileInstaller();
        $moduleFileInstaller->install($module);
    }

    /**
     * Deinstalliert nur das Modul (versionName, Version), wenn es installiert und nicht mehr als abhänigkeit von einem
     * anderen Modul benötigt wird. Es werden keine Abhängigkeiten deinstalliert.
     *
     * Mit der force Option, kann der Abhängigkeits check übersprungen werden und das Modul wird trozdem deinstalliert.
     * Das kann aber zur folge haben, dass andere Module nicht mehr funktionieren.
     */
    public function uninstall(Module $module, bool $force = false): bool
    {
        $this->unsaveModuleInstaller->uninstall($module, $force);

        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();

        return true;
    }
}
