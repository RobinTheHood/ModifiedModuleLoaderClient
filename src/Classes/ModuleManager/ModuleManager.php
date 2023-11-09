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
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\DependencyBuilder;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\SystemSetFactory;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\ModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RuntimeException;

class ModuleManager
{
    /** @var ModuleInstaller */
    private $moduleInstaller;

    /** @var ModuleLoader */
    private $moduleLoader;

    /** @var LocalModuleLoader */
    private $localModuleLoader;

    /** @var DependencyBuilder */
    private $dependencyBuilder;

    /** @var SystemSetFactory */
    private $systemSetFactory;

    public static function create(int $mode): ModuleManager
    {
        $moduleInstaller = ModuleInstaller::create($mode);
        $moduleLoader = ModuleLoader::create($mode);
        $localModuleLoader = LocalModuleLoader::create($mode);
        $dependencyBuilder = DependencyBuilder::create($mode);
        $systemSetFactory = SystemSetFactory::create($mode);

        $moduleInstaller = new ModuleManager(
            $moduleInstaller,
            $moduleLoader,
            $localModuleLoader,
            $dependencyBuilder,
            $systemSetFactory
        );

        return $moduleInstaller;
    }

    public static function createFromConfig(): ModuleManager
    {
        return self::create(Config::getDependenyMode());
    }

    public function __construct(
        ModuleInstaller $moduleInstaller,
        ModuleLoader $moduleLoader,
        LocalModuleLoader $localModuleLoader,
        DependencyBuilder $dependencyBuilder,
        SystemSetFactory $systemSetFactory
    ) {
        $this->moduleInstaller = $moduleInstaller;
        $this->moduleLoader = $moduleLoader;
        $this->localModuleLoader = $localModuleLoader;
        $this->dependencyBuilder = $dependencyBuilder;
        $this->systemSetFactory = $systemSetFactory;
    }

    /**
     * Lädt ein Modul vom Server herunter.
     */
    public function pull(string $archiveName, string $versionConstraint, string $version): Module
    {
        if ($versionConstraint) {
            $module = $this->moduleLoader->loadLatestByArchiveNameAndConstraint($archiveName, $versionConstraint);
        } else {
            $module = $this->moduleLoader->loadLatestVersionByArchiveName($archiveName);
        }

        if (!$module) {
            throw new RuntimeException("Can not pull module $archiveName version $versionConstraint");
        }

        return $this->moduleInstaller->pull($module);
    }

    /**
     * Löscht ein Modul das bereits heruntergeladen wurde.
     */
    public function delete(string $archiveName, string $version): void
    {
        $module = $this->localModuleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            throw new RuntimeException("Can not delete module $archiveName version $version");
        }

        $this->moduleInstaller->delete($module, false);
    }

    /**
     * Lädt und installiert ein Modul in das Shop System UND lädt und installiert alle Abhängigkeiten bzw.
     * abhängige Module nach.
     */
    public function install(string $archiveName, $versionConstraint): void
    {
        $systemSet = $this->systemSetFactory->getSystemSet();
        var_dump($systemSet);

        $combinationSatisfyerResult = $this->dependencyBuilder->satisfies($archiveName, $versionConstraint, $systemSet);
        var_dump($combinationSatisfyerResult);
        die();




        if ($versionConstraint) {
            $module = $this->moduleLoader->loadLatestByArchiveNameAndConstraint($archiveName, $versionConstraint);
        } else {
            $module = $this->moduleLoader->loadLatestVersionByArchiveName($archiveName);
        }

        if (!$module) {
            throw new RuntimeException("Can not pull module $archiveName version $versionConstraint");
        }

        $this->moduleInstaller->install($module, false);

        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();
    }

    /**
     * Installiert ein Modul in das Shop System ABER lädt und installiert KEINE Abhängigkeiten / abhängige Module nach.
     * Sind nicht alle Abhängigkeiten erfüllt, wird nicht installiert und eine Exception geworfen.
     *
     * @param bool $skipDependencyCheck skip dependency check.
     */
    public function installWithoutDependencies(
        string $archiveName,
        string $versionConstraint,
        bool $skipDependencyCheck = false
    ): void {
        $this->moduleInstaller->installWithoutDependencies($module, $skipDependencyCheck, false);

        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();
    }

    /**
     * Aktuallisiert das Modul auf die neuste Version. Dabei werden keine Abhänggigkeiten
     * aktualisiert. Kommen durch das Update jedoch neue Abhänigkeiten hinzu, werden diese installt. Können nicht alle
     * Abhängigkeiten erfüllt werten, wird nicht aktualisiert und eine Exception geworfen.
     */
    public function update(string $archiveName): Module
    {
        $newModule = $this->moduleInstaller->update($module, false);

        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();

        return $newModule;
    }

    /**
     * Aktualiseirt NUR das Modul auf die neuste Version. Es werden keine fehlenden Abhänggigkeiten
     * installiert. Es werden keine Abhänggigkeiten aktualisiert. Können nicht alle Abhängigkeiten erfüllt werten,
     * wird nicht aktualisiert und eine Exception geworfen.
     */
    public function updateWithoutMissingDependencies(string $archvieName, bool $skipDependencyCheck = false): Module
    {
        $loadedNewModul = $this->moduleInstaller->updateWithoutMissingDependencies(
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
    public function discard(string $archiveName, bool $withTemplate = false): void
    {
        $this->moduleInstaller->discard($module, $withTemplate, false);
    }

    /**
     * Deinstalliert nur das Modul, wenn es installiert und nicht mehr als abhänigkeit von einem anderen Modul benötigt
     * wird. Es werden keine Abhängigkeiten deinstalliert.
     *
     * Mit der force Option, kann der Abhängigkeits check übersprungen werden und das Modul wird trozdem deinstalliert.
     * Das kann aber zur folge haben, dass andere Module nicht mehr funktionieren.
     */
    public function uninstall(string $archiveName, bool $force = false): bool
    {
        $this->moduleInstaller->uninstall($module, $force);

        $autoloadFileCreator = new AutoloadFileCreator();
        $autoloadFileCreator->createAutoloadFile();

        return true;
    }
}
