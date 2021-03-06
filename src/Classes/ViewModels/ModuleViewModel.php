<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient\ViewModels;

use RobinTheHood\ModifiedModuleLoaderClient\Module;

class ModuleViewModel
{
    private $module;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    public function getUpdateUrl(string $ref = ''): string
    {
        return $this->getUrl('update', $ref);
    }

    public function getInstallUrl(string $ref = ''): string
    {
        return $this->getUrl('install', $ref);
    }

    public function getLoadAndIntallUrl(string $ref = ''): string
    {
        return $this->getUrl('loadAndInstall', $ref);
    }


    public function getUninstallUrl(string $ref = ''): string
    {
        return $this->getUrl('uninstall', $ref);
    }

    public function getModuleInfoUrl(string $ref = ''): string
    {
        return $this->getUrl('moduleInfo', $ref);
    }

    public function getLoadModuleUrl(string $ref = ''): string
    {
        return $this->getUrl('loadRemoteModule', $ref);
    }

    public function getUnloadModuleUrl(string $ref = ''): string
    {
        return $this->getUrl('unloadLocalModule', $ref);
    }

    private function getUrl(string $action, string $ref): string
    {
        return
            '?action=' . $action .
            '&archiveName=' . $this->module->getArchiveName() .
            '&version=' . $this->module->getVersion() .
            '&ref=' . $ref;
    }
}
