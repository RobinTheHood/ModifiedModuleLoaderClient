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

namespace RobinTheHood\ModifiedModuleLoaderClient\ModuleManager;

use RobinTheHood\ModifiedModuleLoaderClient\Config;
use RobinTheHood\ModifiedModuleLoaderClient\Module;

class ModuleManager
{
    /** @var ModuleInstaller */
    private $unsaveModuleInstaller;

    public static function create(int $mode): ModuleManager
    {
        $unsaveModuleInstaller = ModuleInstaller::create($mode);
        $moduleInstaller = new ModuleManager(
            $unsaveModuleInstaller
        );
        return $moduleInstaller;
    }

    public static function createFromConfig(): ModuleManager
    {
        return self::create(Config::getDependenyMode());
    }

    public function __construct(
        ModuleInstaller $unsaveModuleInstaller
    ) {
        $this->unsaveModuleInstaller = $unsaveModuleInstaller;
    }

    /**
     * Lädt ein Modul (archiveName, Version) vom Server herunter.
     */
    public function pull(Module $module): Module
    {
        return $this->unsaveModuleInstaller->pull($module);
    }

    /**
     * Löscht ein Modul (archiveName, Version) das bereits heruntergeladen wurde.
     */
    public function delete(Module $module): void
    {
        $this->unsaveModuleInstaller->delete($module, false);
    }

    /**
     * Installiert ein Modul (archiveName, Version) in das Shop System UND lädt und installiert alle Abhängigkeiten bzw.
     * abhängige Module nach.
     */
    public function install(Module $module): void
    {
        $this->unsaveModuleInstaller->install($module, false);

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
        $this->unsaveModuleInstaller->installWithoutDependencies($module, $skipDependencyCheck, false);

        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();
    }

    /**
     * Aktuallisiert das Modul (vendorName) auf die neuste Version. Dabei werden keine Abhänggigkeiten
     * aktualisiert. Kommen durch das Update jedoch neue Abhänigkeiten hinzu, werden diese installt. Können nicht alle
     * Abhängigkeiten erfüllt werten, wird nicht aktualisiert und eine Exception geworfen.
     */
    public function update(Module $module): Module
    {
        $newModule = $this->unsaveModuleInstaller->update($module, false);

        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();

        return $newModule;
    }

    /**
     * Aktualiseirt NUR das Modul (vendorName) auf die neuste Version. Es werden keine fehlenden Abhänggigkeiten
     * installiert. Es werden keine Abhänggigkeiten aktualisiert. Können nicht alle Abhängigkeiten erfüllt werten,
     * wird nicht aktualisiert und eine Exception geworfen.
     */
    public function updateWithoutMissingDependencies(Module $module, bool $skipDependencyCheck = false): Module
    {
        $loadedNewModul = $this->unsaveModuleInstaller->updateWithoutMissingDependencies(
            $module,
            $skipDependencyCheck,
            false
        );

        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();

        return $loadedNewModul;
    }

    /**
     * Entfernt alle Änderungen die an den Modul-Dateien im Shop gemacht wurden. Änderungen an Template Dateien werden
     * nicht rückgängig gemacht.
     */
    public function discard(Module $module, $withTemplate = false): void
    {
        $this->unsaveModuleInstaller->discard($module, $withTemplate, false);
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
