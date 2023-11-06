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
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;

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

        echo "Watching the directory " . TextRenderer::color('Modules', TextRenderer::COLOR_GREEN) . " ...\n";

        $dircectoryWatcher = new DirectoryWatcher();
        $dircectoryWatcher->init($directory);
        $dircectoryWatcher->watch(function ($directoryWatcher) use ($basePath) {
            $changes = $directoryWatcher->getChanges();

            if (!$changes) {
                return;
            }

            foreach ($changes as $filePath => $status) {
                $relativeFilePath = FileHelper::stripBasePath($basePath, $filePath);

                if ($status === DirectoryWatcher::STATUS_NEW) {
                    echo TextRenderer::color('File added:', TextRenderer::COLOR_GREEN) . " $relativeFilePath\n";
                } elseif ($status === DirectoryWatcher::STATUS_CHANGED) {
                    echo TextRenderer::color('File modified:', TextRenderer::COLOR_YELLOW) . " $relativeFilePath\n";
                } elseif ($status === DirectoryWatcher::STATUS_DELETED) {
                    echo TextRenderer::color('File deleted:', TextRenderer::COLOR_RED) . " $relativeFilePath\n";
                }

                if (basename($filePath) === 'modulehash.json') {
                    echo "do nothing, modulehash.json is a ignored file\n";
                    continue;
                }

                $module = $this->getInstalledModulByFilePath($filePath);

                if (!$module) {
                    return;
                }

                echo 'Detected installed module: '
                    . $module->getArchiveName() . ' version: ' . $module->getVersion() . "\n";

                // Apply changes
                echo 'Apply changes to ' . $module->getArchiveName() . "\n";
                $moduleInstaller = ModuleInstaller::createFromConfig();
                $moduleInstaller->revertChanges($module);
                $directoryWatcher->reset();
            }
        });
    }

    public function runHelp(MmlcCli $cli): void
    {
        TextRenderer::renderHelpHeading('Description:');
        echo "  Lorem ...\n";
        echo "\n";

        TextRenderer::renderHelpHeading('Usage:');
        echo "  watch ...\n";
        echo "\n";

        TextRenderer::renderHelpHeading('Options:');
        TextRenderer::renderHelpOption('h', 'help', 'Display help for the given command.');
        echo "\n";

        echo "Read more at https://module-loader.de/documentation.php\n";
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

        $localModuleLoader = LocalModuleLoader::create(Comparator::CARET_MODE_STRICT);
        $localModuleLoader->resetCache();
        $modules = $localModuleLoader->loadAllVersionsByArchiveName($archiveName);

        // Check if the module is installed
        $moduleFilter = ModuleFilter::create(Comparator::CARET_MODE_STRICT);
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
