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

use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;

class AutoloadFileCreator
{
    // TODO: In createAutoloadFile() Exceptions werfen im Fehlerfall
    public function createAutoloadFile(): void
    {
        $installedLocalModules = $this->getInstalledModules();
        $autoloadFileContent = $this->buildAutoloadFile($installedLocalModules);
        $this->writeAutoloadFile($autoloadFileContent);
    }

    private function getInstalledModules()
    {
        $localModuleLoader = LocalModuleLoader::createFromConfig();
        $localModuleLoader->resetCache();
        $installedModules = $localModuleLoader->loadAllInstalledVersions();
        return $installedModules;
    }

    private function buildAutoloadFile(array $installedModules): string
    {
        $namespaceEntrys = [];
        foreach ($installedModules as $installedModule) {
            $autoload = $installedModule->getAutoload();

            if (!$autoload) {
                continue;
            }

            if (!$autoload['psr-4']) {
                continue;
            }

            foreach ($autoload['psr-4'] as $namespace => $path) {
                $path = str_replace(
                    $installedModule->getSourceMmlcDir(),
                    'vendor-mmlc/' . $installedModule->getArchiveName(),
                    $path
                );

                $namespaceEntrys[] =
                    '$loader->setPsr4(\'' . $namespace . '\\\', DIR_FS_DOCUMENT_ROOT . \'' . $path . '\');';
            }
        }

        $namespaceEntrys = array_unique($namespaceEntrys);
        $namespaceMapping = implode("\n", $namespaceEntrys);

        $template = file_get_contents(App::getTemplatesRoot() . '/autoload.php.tmpl');
        $autoloadFileContent = str_replace('{VENDOR_PSR4_NAMESPACE_MAPPINGS}', $namespaceMapping, $template);

        return $autoloadFileContent;
    }

    private function writeAutoloadFile(string $autoloadFileContent): void
    {
        if (!file_exists(App::getShopRoot() . '/vendor-no-composer')) {
            mkdir(App::getShopRoot() . '/vendor-no-composer');
        }
        file_put_contents(App::getShopRoot() . '/vendor-no-composer/autoload.php', $autoloadFileContent);

        if (!file_exists(App::getShopRoot() . '/vendor-mmlc')) {
            mkdir(App::getShopRoot() . '/vendor-mmlc');
        }
        file_put_contents(App::getShopRoot() . '/vendor-mmlc/autoload.php', $autoloadFileContent);
    }
}
