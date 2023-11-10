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

namespace RobinTheHood\ModifiedModuleLoaderClient\Cli\Command;

use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\DirectoryWatcher;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\MmlcCli;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\TextRenderer;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFilter;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleInstaller;

class CommandWatch implements CommandInterface
{
    public function __construct()
    {
    }

    public function getName(): string
    {
        return 'watch';
    }

    public function run(MmlcCli $cli): void
    {
        $basePath = App::getModulesRoot();
        $directory = App::getModulesRoot();

        $cli->writeLine("Watching the directory " . TextRenderer::color('Modules', TextRenderer::COLOR_GREEN) . " ...");

        $dircectoryWatcher = new DirectoryWatcher();
        $dircectoryWatcher->init($directory);
        $dircectoryWatcher->watch(function ($directoryWatcher) use ($cli, $basePath) {
            $changes = $directoryWatcher->getChanges();

            if (!$changes) {
                return;
            }

            foreach ($changes as $filePath => $status) {
                $relativeFilePath = FileHelper::stripBasePath($basePath, $filePath);

                if ($status === DirectoryWatcher::STATUS_NEW) {
                    $cli->writeLine(
                        TextRenderer::color('File added:', TextRenderer::COLOR_GREEN) . " $relativeFilePath"
                    );
                } elseif ($status === DirectoryWatcher::STATUS_CHANGED) {
                    $cli->writeLine(
                        TextRenderer::color('File modified:', TextRenderer::COLOR_YELLOW) . " $relativeFilePath"
                    );
                } elseif ($status === DirectoryWatcher::STATUS_DELETED) {
                    $cli->writeLine(
                        TextRenderer::color('File deleted:', TextRenderer::COLOR_RED) . " $relativeFilePath"
                    );
                }

                if (basename($filePath) === 'modulehash.json') {
                    $cli->writeLine("do nothing, modulehash.json is a ignored file");
                    continue;
                }

                $module = $this->getInstalledModulByFilePath($filePath);

                if (!$module) {
                    return;
                }

                $cli->writeLine(
                    'Detected installed module: '
                    . $module->getArchiveName() . ' version: ' . $module->getVersion()
                );

                // Apply changes
                $cli->writeLine('Apply changes to ' . $module->getArchiveName());
                $moduleInstaller = ModuleInstaller::createFromConfig();
                $moduleInstaller->revertChanges($module);
                $directoryWatcher->reset();
            }
        });

        return;
    }

    public function getHelp(MmlcCli $cli): string
    {
        return
            TextRenderer::renderHelpHeading('Description:')
            . "  Lorem ...\n"
            . "\n"

            . TextRenderer::renderHelpHeading('Usage:')
            . "  watch ...\n"
            . "\n"

            . TextRenderer::renderHelpHeading('Options:')
            . TextRenderer::renderHelpOption('h', 'help', 'Display help for the given command.')
            . "\n"

            . "Read more at https://module-loader.de/documentation.php";
    }

    /**
     * Liefert ein lokales Modul anhand eines Dateipfades. Die Methode überprüft, ob die angegebene Datei zu einem
     * lokale installierten Modul gehört und liefert ein Modul Objekt zurück oder null.
     */
    private function getInstalledModulByFilePath(string $filePath): ?Module
    {
        $archiveName = $this->getArchiveNameFromFilePath($filePath);

        if (!$archiveName) {
            return null;
        }

        $localModuleLoader = LocalModuleLoader::createFromConfig();
        $localModuleLoader->resetCache();
        $modules = $localModuleLoader->loadAllVersionsByArchiveName($archiveName);

        // Check if the module is installed
        $moduleFilter = ModuleFilter::createFromConfig();
        $installedModules = $moduleFilter->filterInstalled($modules);

        if (!$installedModules) {
            return null;
        }

        return $installedModules[0];
    }

    /**
     * Liefert den archiveName von einem Dateipath.
     */
    private function getArchiveNameFromFilePath(string $filePath): string
    {
        $basePath = App::getModulesRoot();
        $relativeFilePath = FileHelper::stripBasePath($basePath, $filePath);
        $relativeFilePath = ltrim($relativeFilePath, \DIRECTORY_SEPARATOR);

        $parts = explode(\DIRECTORY_SEPARATOR, $relativeFilePath);
        $vendorName = $parts[0] ?? '';
        $moduleName = $parts[1] ?? '';

        if (!$vendorName) {
            return '';
        }

        if (!$moduleName) {
            return '';
        }

        // Now we can create our archiveName
        $archiveName = $vendorName . '/' . $moduleName;
        return $archiveName;
    }
}
