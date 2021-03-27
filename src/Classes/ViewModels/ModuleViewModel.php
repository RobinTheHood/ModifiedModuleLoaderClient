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

    public function getRevertChangesUrl(string $ref = ''): string
    {
        return $this->getUrl('revertChanges', $ref);
    }

    public function getLoadAndInstallUrl(string $ref = ''): string
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

    public function getPriceFormated(): string
    {
        if ($this->module->getPrice() === 'free') {
            return '<span class="price-free">kostenlos</span>';
        } elseif (!$this->module->getPrice()) {
            return '<span class="price-request">Preis auf Anfrage</span>';
        } else {
            return
                '<span class="price-normal">' .
                    number_format((float) $this->module->getPrice(), 2, ',', '.') . ' € ' .
                '</span>';
        }
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
