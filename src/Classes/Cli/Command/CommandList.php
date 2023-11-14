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

use RobinTheHood\ModifiedModuleLoaderClient\Cli\MmlcCli;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\TextRenderer;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\RemoteModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFilter;
use RobinTheHood\ModifiedModuleLoaderClient\Module;

class CommandList implements CommandInterface
{
    private const FILTER_NO = 0;
    private const FILTER_ALL = 1;
    private const FILTER_ARCHIVENAME = 2;
    private const FILTER_NAME = 3;
    private const FILTER_SHORT_DESCRIPTION = 4;

    public function __construct()
    {
    }

    public function getName(): string
    {
        return 'list';
    }

    public function run(MmlcCli $cli): void
    {
        if ($cli->hasOption('--all') || $cli->hasOption('-a')) {
            $this->listAll($cli);
        } elseif ($cli->hasOption('--downloadable') || $cli->hasOption('-d')) {
            $this->listDownloadable($cli);
        } elseif ($cli->hasOption('--installed') || $cli->hasOption('-i')) {
            $this->listInstalled($cli);
        } elseif ($cli->hasOption('--pulled') || $cli->hasOption('-p')) {
            $this->listPulled($cli);
        } elseif ($cli->hasOption('--plulledall') || $cli->hasOption('-P')) {
            $this->listPulledAll($cli);
        } elseif ($cli->hasOption('--changed') || $cli->hasOption('-c')) {
            $this->listChanged($cli);
        } else {
            $this->listInstalled($cli);
        }

        return;
    }

    /**
     * Show all remote modules
     *
     * Output-Text example:
     * mycompany/myfirstmodule
     * grandlejay/dhl
     * firstweb/seo-url
     */
    private function listAll(MmlcCli $cli): void
    {
        $remoteModuleLoader = RemoteModuleLoader::create();
        $modules = $remoteModuleLoader->loadAllLatestVersions();

        $filterMethod = $this->getFilterMethod($cli);
        $filterMethod = $filterMethod === self::FILTER_NO ? self::FILTER_ALL : $filterMethod;
        $searchWord = $cli->getFilteredArgument(0);

        if ($filterMethod !== self::FILTER_NO && $searchWord) {
            $modules = $this->filterModules($modules, $searchWord, $filterMethod);
        }

        foreach ($modules as $module) {
            $cli->writeLine(
                TextRenderer::rightPad($module->getArchiveName(), 30)
                . $module->getName()
            );
        }
    }

    /**
     * Show all remote modules that can be downloaded
     *
     * Output-Text example:
     * vendorname/modulename
     * mycompany/myfirstmodule
     * grandlejay/dhl
     */
    private function listDownloadable(MmlcCli $cli): void
    {
        $remoteModuleLoader = RemoteModuleLoader::create();
        $modules = $remoteModuleLoader->loadAllLatestVersions();

        $filterMethod = $this->getFilterMethod($cli);
        $filterMethod = $filterMethod === self::FILTER_NO ? self::FILTER_ALL : $filterMethod;
        $searchWord = $cli->getFilteredArgument(0);

        if ($filterMethod !== self::FILTER_NO && $searchWord) {
            $modules = $this->filterModules($modules, $searchWord, $filterMethod);
        }

        foreach ($modules as $module) {
            if (!$module->isLoadable()) {
                continue;
            }

            $cli->writeLine(
                TextRenderer::rightPad($module->getArchiveName(), 30)
                . $module->getName()
            );
        }
    }

    /**
     * Show all installed modules
     *
     * Output-Text example:
     * mycompany/myfirstmodule  1.1.1      A very nice first Module
     * grandlejay/dhl           1.0.2      Adds a DHL shipping method
     */
    private function listInstalled(MmlcCli $cli): void
    {
        $localModuleLoader = LocalModuleLoader::createFromConfig();
        $modules = $localModuleLoader->loadAllInstalledVersions();

        $filterMethod = $this->getFilterMethod($cli);
        $filterMethod = $filterMethod === self::FILTER_NO ? self::FILTER_ALL : $filterMethod;
        $searchWord = $cli->getFilteredArgument(0);

        if ($filterMethod !== self::FILTER_NO && $searchWord) {
            $modules = $this->filterModules($modules, $searchWord, $filterMethod);
        }

        foreach ($modules as $module) {
            $cli->writeLine(
                TextRenderer::rightPad($module->getArchiveName(), 40) . " "
                . TextRenderer::rightPad($module->getVersion(), 10) . " "
                . $module->getShortDescription()
            );
        }
    }

    /**
     * Show all modules that have been pulled
     *
     * Output-Text example:
     * mycompany/myfirstmodule     A very nice first Module
     * grandlejay/dhl              Adds a DHL shipping method.
     */
    private function listPulled(MmlcCli $cli): void
    {
        $localModuleLoader = LocalModuleLoader::createFromConfig();
        $modules = $localModuleLoader->loadAllVersions();

        $moduleFilter = ModuleFilter::createFromConfig();
        $modules = $moduleFilter->filterNewestVersion($modules);

        foreach ($modules as $module) {
            $cli->writeLine(
                TextRenderer::rightPad($module->getArchiveName(), 40) . " "
                . $module->getShortDescription()
            );
        }
    }

    /**
     * Show every version of modules that have been pulled
     *
     * Output-Text example:
     * mycompany/myfirstmodule  1.1.1
     * mycompany/myfirstmodule  1.1.2
     * mycompany/myfirstmodule  1.1.3
     * grandlejay/dhl           1.0.0
     * grandlejay/dhl           1.0.1
     */
    private function listPulledAll(MmlcCli $cli): void
    {
        $localModuleLoader = LocalModuleLoader::createFromConfig();
        $modules = $localModuleLoader->loadAllVersions();

        foreach ($modules as $module) {
            $cli->writeLine(
                TextRenderer::rightPad($module->getArchiveName(), 40) . " "
                . $module->getVersion()
            );
        }
    }

    /**
     * Show all installed modules that have been changed
     *
     * Output-Text example:
     * mycompany/myfirstmodule  1.1.1      A very nice first Module
     */
    private function listChanged(MmlcCli $cli): void
    {
        $localModuleLoader = LocalModuleLoader::createFromConfig();
        $modules = $localModuleLoader->loadAllInstalledVersions();

        foreach ($modules as $module) {
            if (!$module->isChanged()) {
                continue;
            }

            $cli->writeLine(
                TextRenderer::rightPad($module->getArchiveName(), 40) . " "
                . TextRenderer::rightPad($module->getVersion(), 10) . " "
                . $module->getShortDescription()
            );
        }
    }


    private function getFilterMethod(MmlcCli $cli): int
    {
        if ($cli->hasOption('--in=archivename')) {
            return self::FILTER_ARCHIVENAME;
        }

        if ($cli->hasOption('--in=name')) {
            return self::FILTER_NAME;
        }

        if ($cli->hasOption('--in=shortdesc')) {
            return self::FILTER_SHORT_DESCRIPTION;
        }

        if ($cli->hasOption('--in=all')) {
            return self::FILTER_ALL;
        }

        return self::FILTER_NO;
    }

    /**
     * @param Module[] $modules
     * @param string $searchWord
     * @param int $filterMethod
     *
     * @return Module[]
     */
    private function filterModules(array $modules, string $searchWord, int $filterMethod): array
    {
        if ($filterMethod === self::FILTER_ARCHIVENAME) {
            return $this->filterModuleByArchiveName($modules, $searchWord);
        } elseif ($filterMethod === self::FILTER_NAME) {
            return $this->filterModuleByName($modules, $searchWord);
        } elseif ($filterMethod === self::FILTER_SHORT_DESCRIPTION) {
            return $this->filterModuleByShortDescription($modules, $searchWord);
        } else {
            return $this->filterModuleByAll($modules, $searchWord);
        }
    }

    /**
     * @param Module[] $modules
     * @param string $searchWord
     *
     * @return Module[]
     */
    private function filterModuleByAll(array $modules, string $searchWord): array
    {
        $modulesByArchiveName = $this->filterModuleByArchiveName($modules, $searchWord);
        $modulesByName = $this->filterModuleByName($modules, $searchWord);
        $modulesByShortDescription = $this->filterModuleByShortDescription($modules, $searchWord);

        $filteredModules = array_merge(
            $modulesByArchiveName,
            $modulesByName,
            $modulesByShortDescription
        );

        return $this->filterUnique($filteredModules);
    }

    /**
     * @param Module[] $modules
     * @param string $searchWord
     *
     * @return Module[]
     */
    private function filterModuleByArchiveName(array $modules, string $searchWord): array
    {
        $fileredModules = [];
        foreach ($modules as $module) {
            if (stripos($module->getArchiveName(), $searchWord) === false) {
                continue;
            }
            $fileredModules[] = $module;
        }
        return $fileredModules;
    }

    /**
     * @param Module[] $modules
     * @param string $searchWord
     *
     * @return Module[]
     */
    private function filterModuleByName(array $modules, string $searchWord): array
    {
        $fileredModules = [];
        foreach ($modules as $module) {
            if (stripos($module->getName(), $searchWord) === false) {
                continue;
            }
            $fileredModules[] = $module;
        }
        return $fileredModules;
    }

    /**
     * @param Module[] $modules
     * @param string $searchWord
     *
     * @return Module[]
     */
    private function filterModuleByShortDescription(array $modules, string $searchWord): array
    {
        $fileredModules = [];
        foreach ($modules as $module) {
            if (stripos($module->getName(), $searchWord) === false) {
                continue;
            }
            $fileredModules[] = $module;
        }
        return $fileredModules;
    }

    /**
     * @param Module[] $modules
     *
     * @return Module[]
     */
    private function filterUnique(array $modules): array
    {
        $filteredModules = [];
        foreach ($modules as $module) {
            $filteredModules[$module->getArchiveName()] = $module;
        }
        return $filteredModules;
    }

    public function getHelp(MmlcCli $cli): string
    {
        return
            TextRenderer::renderHelpHeading('Description:')
            . "  List all available modules that can be used with MMLC.\n"
            . "\n"

            . TextRenderer::renderHelpHeading('Usage:')
            . "  list [searchword]"
            . "\n\n"

            . TextRenderer::renderHelpHeading('Arguments:')
            . TextRenderer::renderHelpArgument('searchword', 'Filters the output by a search term.')
            . "\n\n"

            . TextRenderer::renderHelpHeading('Options:')
            . TextRenderer::renderHelpOption('a', 'all', 'Show all remote modules.')
            . TextRenderer::renderHelpOption('d', 'downloadable', 'Only show modules that are downloadable.')
            . TextRenderer::renderHelpOption('i', 'installed', '(default) Only show modules that are installed.')
            . TextRenderer::renderHelpOption('p', 'pulled', 'Only show modules that are downloaded.')
            . TextRenderer::renderHelpOption('P', 'pulledall', 'Show all downloaded versions.')
            . TextRenderer::renderHelpOption('c', 'changed', 'Show all changed installed modules.')
            . TextRenderer::renderHelpOption('f', 'format=FORMAT', 'Format of the output. [text, json].')
            . TextRenderer::renderHelpOption('', 'in=VALUE', 'If a search term is provided, search only in the specified fields. [name, archivename, sortdesc, all].')

            . TextRenderer::renderHelpOption('h', 'help', 'Display help for the given command.')
            . "\n"

            . "Read more at https://module-loader.de/documentation.php";
    }
}
