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
use RobinTheHood\ModifiedModuleLoaderClient\Module;

class AutoloadFileCreator
{
    public function createAutoloadFile(): void
    {
        $installedLocalModules = $this->getInstalledModules();
        $autoloadFileContent = $this->buildAutoloadFile($installedLocalModules);
        $this->writeAutoloadFile($autoloadFileContent);
    }

    /**
     * @return Module[]
     */
    private function getInstalledModules(): array
    {
        $localModuleLoader = LocalModuleLoader::createFromConfig();
        $localModuleLoader->resetCache();
        $installedModules = $localModuleLoader->loadAllInstalledVersions();
        return $installedModules;
    }

    private function convertAutoloadEntryToPsr4AutoloadPhp(AutoloadEntry $autoloadEntry): string
    {
        $namespace = $autoloadEntry->namespace;
        $realPath = $autoloadEntry->realPath;
        $php = '$loader->setPsr4(\'' . $namespace . '\\\', DIR_FS_DOCUMENT_ROOT . \'' . $realPath . '\');';
        return $php;
    }

    /**
     * @param Module[] $installedModules
     */
    private function buildAutoloadFile(array $installedModules): string
    {
        $autoloadEntryCollection = AutoloadEntryCollection::createFromModules($installedModules);
        $autoloadEntryCollection = $autoloadEntryCollection->unique();

        $phpEntries = [];
        foreach ($autoloadEntryCollection as $autoloadEntry) {
            $phpEntries[] = $this->convertAutoloadEntryToPsr4AutoloadPhp($autoloadEntry);
        }
        $phpCodeNamespaceMappings = implode("\n", $phpEntries);

        $template = file_get_contents(App::getTemplatesRoot() . '/autoload.php.tmpl');
        $autoloadFileContent = str_replace('{VENDOR_PSR4_NAMESPACE_MAPPINGS}', $phpCodeNamespaceMappings, $template);

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
