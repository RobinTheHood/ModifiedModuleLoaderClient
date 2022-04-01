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

namespace RobinTheHood\ModifiedModuleLoaderClient;

use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\Config;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\ParseErrorException;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;
use RobinTheHood\ModifiedModuleLoaderClient\Api\V1\HttpRequest;
use RobinTheHood\ModifiedModuleLoaderClient\Api\V1\ApiRequest;

class SelfUpdater
{
    /**
     * @var string
     */
    private $appRoot = '';

    /**
     * @var string
     */
    private $remoteUpdateServer;

    /**
     * @var Comparator
     */
    protected $comparator;

    public function __construct()
    {
        // appRoot wird in die Variable ausgelagert, da während der Installation,
        // wenn Dateien verschoben werden, die Methode App::getRoot() nicht
        // mehr richtige Ergebnisse liefert.
        $remoteAddress = Config::getRemoteAddress() ?? '';

        if (empty(Config::getRemoteAddress())) {
            throw new \RuntimeException('Unable to connect. RemoteAddress is empty or not set.');
        }

        $this->remoteUpdateServer = str_replace('/api.php', '/Downloads/', $remoteAddress);
        $this->appRoot = App::getRoot();
        $this->comparator = new Comparator(new Parser());
    }


    public function checkUpdate(): bool
    {
        $newestVersionInfo = $this->getNewestVersionInfo();
        $installedVersion = $this->getInstalledVersion();

        try {
            if ($this->comparator->greaterThan($newestVersionInfo['version'], $installedVersion)) {
                return true;
            }
        } catch (ParseErrorException $e) {
            // do nothing
        }

        return false;
    }

    public function getVersionInfos(): array
    {
        $apiRequest = new ApiRequest();
        $result = $apiRequest->getAllVersions();

        $content = $result['content'] ?? [];
        if (!$content) {
            return [];
        }

        return $content;
    }

    public function getInstalledVersion(): string
    {
        $json = file_get_contents($this->appRoot . '/config/version.json');
        $version = json_decode($json);
        if ($version) {
            return $version->version;
        }
        return ''; // Better throw an exception
    }

    /**
     * @return array<string, string> Returns the latest version info
     */
    public function getNewestVersionInfo(): array
    {
        $versionInfos = $this->getVersionInfos();

        $newestVersionInfo = ['fileName' => '', 'version' => '0.0.0-alpha'];

        foreach ($versionInfos as $versionInfo) {
            try {
                $version = (new Parser())->parse($versionInfo['version']);

                if (Config::getSelfUpdate() != 'latest' && $version->getTag()) {
                    continue;
                }

                if ($this->comparator->greaterThan($versionInfo['version'], $newestVersionInfo['version'])) {
                    $newestVersionInfo = $versionInfo;
                }
            } catch (ParseErrorException $e) {
                // do nothing
            }
        }

        return $newestVersionInfo;
    }

    public function getFileNameByVersion(string $version): string
    {
        $versionInfos = $this->getVersionInfos();
        $installFileName = '';
        foreach ($versionInfos as $versionInfo) {
            if ($versionInfo['version'] == $version) {
                return $versionInfo['fileName'];
            }
        }
        return '';
    }

    public function update(string $installVersion): void
    {
        $installFileName = $this->getFileNameByVersion($installVersion);
        if (!$installFileName) {
            return;
        }

        $this->download($installFileName);
        $this->backup($installFileName);
        $this->untar($installFileName);
        $this->install();
        $this->setupConfig();
    }

    public function download(string $fileName): bool
    {
        $remoteAddress = $this->remoteUpdateServer . $fileName;

        $httpRequest = new HttpRequest();
        $tarBall = $httpRequest->sendGetRequest($remoteAddress);

        if (!$tarBall) {
            die("Error: Can not download $remoteAddress file. <a href=\"?action=selfUpdate\">back</a>");
            return false;
        }

        file_put_contents($this->appRoot . '/' . $fileName, $tarBall);
        return true;
    }

    public function backup(string $installFileName): void
    {
        $srcPath = $this->appRoot;
        $destPath = $this->appRoot . '/backup';
        @mkdir($destPath);

        $exclude = [
            '/Archives',
            '/Modules',
            '/backup',
            '/' . $installFileName
        ];

        $files = FileHelper::scanDir($srcPath, FileHelper::FILES_AND_DIRS, true);
        FileHelper::moveFilesTo($files, $srcPath, $destPath, $exclude);
    }

    public function untar(string $installFileName): void
    {
        $tarFilePath = $this->appRoot . '/' . $installFileName;

        $tarBall = new \PharData($installFileName);
        $tarBall->extractTo($this->appRoot, null, true);

        system('rm -rf ' . $tarFilePath);
    }

    public function install(): void
    {
        $srcPath = $this->appRoot . '/ModifiedModuleLoaderClient';
        $destPath = $this->appRoot;

        $files = FileHelper::scanDir($srcPath, FileHelper::FILES_AND_DIRS, true);
        FileHelper::moveFilesTo($files, $srcPath, $destPath);

        system('rm -rf ' . $srcPath);
    }

    public function setupConfig(): void
    {
        @unlink($this->appRoot . '/config/config.php');
        @copy($this->appRoot . '/backup/config/config.php', $this->appRoot . '/config/config.php');
    }


    public function checkAndDoPostUpdate(): bool
    {
        if (file_exists($this->appRoot . '/config/postUpdate')) {
            return false;
        }

        $this->postUpdate();
        system('rm -rf ' . $this->appRoot . '/backup');

        file_put_contents($this->appRoot . '/config/postUpdate', "SelfUpdate::postUpdate() DONE");
        return true;
    }

    public function postUpdate(): void
    {
        // Vor der Version 1.12.0 haben sich die config.php und die version.json
        // im Root-Verzeichnis befunden und der alte SelfUpdater hat nicht alle
        // Dateien einer neuen Version kopiert. Der neue SelfUpdater kopiert zwar
        // jetzt alle Dateien, jedoch müssen einige Dateien noch manuell
        // erstellt oder kopiert werden, falls der alte SelfUpdater diese Dateien
        // ausgelassen hat.

        // Änderungen ab Version 1.12.0 korrigieren
        if (!file_exists($this->appRoot . '/config')) {
            mkdir($this->appRoot . '/config');
        }

        // *** config/config.json ***
        $dest = $this->appRoot . '/config/config.php';

        if (!file_exists($dest) && file_exists($this->appRoot . '/config.php')) {
            rename($this->appRoot . 'config.php', $dest);
        }

        // *** config/version.json ***
        // Wenn die config/version.json Datei fehlt, gibt es 3 Möglichkeiten diese
        // zu erzeugen.
        $dest = $this->appRoot . '/config/version.json';

        if (!file_exists($dest) && file_exists($this->appRoot . '/ModifiedModuleLoaderClient/config/version.json')) {
            rename($this->appRoot . '/ModifiedModuleLoaderClient/config/version.json', $dest);
        }

        if (!file_exists($dest) && file_exists($this->appRoot . '/version.json')) {
            rename($this->appRoot . 'version.json', $dest);
        }

        if (!file_exists($dest)) {
            $newestVersionInfo = $this->getNewestVersionInfo();
            file_put_contents($this->appRoot . '/config/version.json', '{"version": "' . $newestVersionInfo['version'] . '"}');
        }
    }
}
