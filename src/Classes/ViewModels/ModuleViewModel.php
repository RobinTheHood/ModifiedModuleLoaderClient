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

namespace RobinTheHood\ModifiedModuleLoaderClient\ViewModels;

use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\GitHelper;
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleStatus;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\ParseErrorException;
use RobinTheHood\ModifiedModuleLoaderClient\ShopInfo;

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

    public function getForceUpdateUrl(string $ref = ''): string
    {
        return $this->getUrl('update', $ref, null, ['force' => 'true']);
    }

    public function getInstallUrl(string $ref = ''): string
    {
        return $this->getUrl('install', $ref);
    }

    public function getForceInstallUrl(string $ref = ''): string
    {
        return $this->getUrl('install', $ref, null, ['force' => 'true']);
    }

    public function getRevertChangesUrl(string $ref = ''): string
    {
        return $this->getUrl('revertChanges', $ref);
    }

    public function getRevertChangesWithTemplateUrl(string $ref = ''): string
    {
        return $this->getUrl('revertChanges', $ref, null, ['withTemplate' => 'true']);
    }

    public function getLoadAndInstallUrl(string $ref = ''): string
    {
        return $this->getUrl('loadAndInstall', $ref);
    }

    public function getUninstallUrl(string $ref = ''): string
    {
        return $this->getUrl('uninstall', $ref);
    }

    public function getForceUninstallUrl(string $ref = ''): string
    {
        return $this->getUrl('uninstall', $ref, null, ['force' => 'true']);
    }

    public function getModuleInfoUrl(string $ref = ''): string
    {
        return $this->getUrl('moduleInfo', $ref);
    }

    public function getInstalledUrl(string $ref = ''): string
    {
        $module = $this->module->getInstalledVersion();
        return $this->getUrl('moduleInfo', $ref, $module);
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

    public function getIconUri(): string
    {
        return $this->module->getIconUri();
    }

    public function getName(): string
    {
        return $this->module->getName();
    }

    public function getImageUris(): array
    {
        return $this->module->getImageUris();
    }

    public function isRepairable(): bool
    {
        return ModuleStatus::isRepairable($this->module);
    }

    public function isLoadable(): bool
    {
        return ModuleStatus::isLoadable($this->module);
    }

    public function isCompatible(): bool
    {
        return $this->module->isCompatible();
    }

    public function isUpdatable(): bool
    {
        return ModuleStatus::isUpdatable($this->module);
    }

    public function isCompatibleLoadableAndInstallable(): bool
    {
        return ModuleStatus::isCompatibleLoadableAndInstallable($this->module);
    }

    public function isIncompatibleLoadebale(): bool
    {
        return ModuleStatus::isIncompatibleLoadebale($this->module);
    }

    public function isUninstallable(): bool
    {
        return ModuleStatus::isUninstallable($this->module);
    }

    public function isCompatibleInstallable(): bool
    {
        return ModuleStatus::isCompatibleInstallable($this->module);
    }

    public function isIncompatibleInstallable(): bool
    {
        return ModuleStatus::isIncompatibleInstallable($this->module);
    }

    public function hasInstalledVersion(): bool
    {
        $installedModule = $this->module->getInstalledVersion();

        if (!$installedModule) {
            return false;
        }

        if ($installedModule->getVersion() == $this->module->getVersion()) {
            return false;
        }

        return true;
    }

    public function getVersion(): string
    {
        return $this->module->getVersion();
    }

    public function getVersionAndGitBranch(): string
    {
        $version = $this->getVersion();

        if ($this->module->isRemote()) {
            return $version;
        }

        $gitHelper = new GitHelper();
        $gitBranch = $gitHelper->getCurrentGitBranch(
            $this->module->getLocalRootPath() . $this->module->getModulePath() . '/.git'
        );

        if ($gitBranch) {
            //return $version . ' 🔀 ' . $gitBranch;
            return $version . ' git:(' . $gitBranch . ')';
        } else {
            return $version;
        }
    }

    public function getDate(): string
    {
        return $this->module->getDate();
    }

    public function isRemote(): bool
    {
        return $this->module->isRemote();
    }

    public function isLoaded(): bool
    {
        return $this->module->isLoaded();
    }

    public function isInstalled(): bool
    {
        return $this->module->isInstalled();
    }

    public function isChanged(): bool
    {
        return $this->module->isChanged();
    }

    /**
     * @param string $action
     * @param string $ref
     * @param ?Module $module
     * @param string[] $queryParams
     *
     * @return string
     */
    private function getUrl(string $action, string $ref, ?Module $module = null, array $queryParams = []): string
    {
        if (!$module) {
            $module = $this->module;
        }

        $queryString = http_build_query($queryParams);
        if ($queryString) {
            $queryString = '&' . $queryString;
        }

        return
            '?action=' . $action .
            '&archiveName=' . $module->getArchiveName() .
            '&version=' . $module->getVersion() .
            '&ref=' . $ref
            . $queryString;
    }

    /**
     * @return array<array>
     */
    public function getCompatibleStrings(): array
    {
        $array = [];

        if (!$this->module->isCompatibleWithModified()) {
            $version = ShopInfo::getModifiedVersion();
            $array[] = [
                'text' => "Dieses Modul wurde nicht mit deiner Version von modified getestet. Du hast modifed Version
                    <strong>$version</strong> installiert.",
                'type' => 'warning'
            ];
        }

        try {
            if (!$this->module->isCompatibleWithPhp()) {
                $version = phpversion();
                $array[] = [
                    'text' => "Dieses Modul wurde noch nicht mit deiner PHP Version getestet. Du verwendest die PHP
                        Version <strong>$version</strong>.",
                    'type' => 'warning'
                ];
            }
        } catch (ParseErrorException $e) {
            $array[] = [
                'text' => 'Error: Can not parse PHP version in moduleinfo.php',
                'type' => 'error'
            ];
        }

        try {
            if (!$this->module->isCompatibleWithMmlc()) {
                $version = App::getMmlcVersion();
                $array[] = [
                    'text' => "Dieses Modul wurde noch nicht mit deiner MMLC Version getestet. Du verwendest die MMLC
                        Version <strong>$version</strong>.",
                    'type' => 'warning'
                ];
            }
        } catch (ParseErrorException $e) {
            $array[] = [
                'text' => 'Error: Can not parse MMLC version in moduleinfo.php',
                'type' => 'error'
            ];
        }

        return $array;
    }
}
