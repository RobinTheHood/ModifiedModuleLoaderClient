<?php

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
use RobinTheHood\ModifiedModuleLoaderClient\Semver;
use RobinTheHood\ModifiedModuleLoaderClient\SemverParser;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\ArrayHelper;
use RobinTheHood\ModifiedModuleLoaderClient\Api\HttpRequest;
use RobinTheHood\ModifiedModuleLoaderClient\Api\Client\ApiRequest;

class SelfUpdater
{
    private $appRoot = '';
    private $remoteUpdateServer = 'https://app.module-loader.de/Downloads/';
    protected $semver;

    public function __construct()
    {
        // appRoot wird in die Variable ausgelagert, da während der Installation,
        // wenn Dateien verschoben werden, die Methode App::getRoot() nicht
        // mehr richtige Ergebnisse liefert.
        $this->appRoot = App::getRoot();
        $this->semver = new Semver(new SemverParser());
    }

    public function checkUpdate()
    {
        $newestVersionInfo = $this->getNewestVersionInfo();
        $installedVersion = $this->getInstalledVersion();

        try {
            if ($this->semver->greaterThan($newestVersionInfo['version'], $installedVersion)) {
                return true;
            }
        } catch (ParseErrorException $e) {}

        return false;
    }

    public function getVersionInfos()
    {
        $apiRequest = new ApiRequest();
        $result = $apiRequest->getAllVersions();

        if (!ArrayHelper::getIfSet($result, 'content')) {
            return [];
        }

        return $result['content'];
    }

    public function getInstalledVersion()
    {
        $json = file_get_contents($this->appRoot . '/config/version.json');
        $version = json_decode($json);
        if ($version) {
            return $version->version;
        }
    }

    public function getNewestVersionInfo()
    {
        $versionInfos = $this->getVersionInfos();

        $newestVersionInfo = ['fileName' => '', 'version' => '0.0.0'];

        foreach ($versionInfos as $versionInfo) {
            try {
                if ($this->semver->greaterThan($versionInfo['version'], $newestVersionInfo['version'])) {
                    $newestVersionInfo = $versionInfo;
                }
            } catch (ParseErrorException $e) {}
        }

        return $newestVersionInfo;
    }

    public function getFileNameByVersion($version)
    {
        $versionInfos = $this->getVersionInfos();
        $installFileName = '';
        foreach($versionInfos as $versionInfo) {
            if ($versionInfo['version'] == $version) {
                return $versionInfo['fileName'];
            }
        }
        return '';
    }

    public function update($installVersion)
    {
        $installFileName = $this->getFileNameByVersion($installVersion);
        if (!$installFileName) {
            return;
        }

        $this->download($installFileName);
        $this->backup($installFileName);
        $this->untar($installFileName);
        $this->install($installFileName);
        $this->setupConfig();
    }

    public function download($fileName)
    {
        $remoteAddress = $this->remoteUpdateServer . $fileName;

        $httpRequest = new HttpRequest();
        $tarBall = $httpRequest->sendGetRequest($remoteAddress);

        if (!$tarBall) {
            return false;
        }

        file_put_contents($this->appRoot . '/' . $fileName, $tarBall);
    }

    public function backup($installFileName)
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

    public function untar($installFileName)
    {
        $tarFilePath = $this->appRoot . '/' . $installFileName;

        $tarBall = new \PharData($installFileName);
        $tarBall->extractTo($this->appRoot, null, true);

        system('rm -rf ' . $tarFilePath);
    }

    public function install()
    {
        $srcPath = $this->appRoot . '/ModifiedModuleLoaderClient';
        $destPath = $this->appRoot;

        $files = FileHelper::scanDir($srcPath, FileHelper::FILES_AND_DIRS, true);
        FileHelper::moveFilesTo($files, $srcPath, $destPath);

        system('rm -rf ' . $srcPath);
    }

    public function setupConfig()
    {
        @unlink($this->appRoot . '/config/config.php');
        @copy($this->appRoot . '/backup/config/config.php', $this->appRoot . '/config/config.php');
    }


    public function checkAndDoPostUpdate()
    {
        if (file_exists($this->appRoot . '/config/postUpdate')) {
            return false;
        }

        $this->postUpdate();
        system('rm -rf ' . $this->appRoot . '/backup');

        file_put_contents($this->appRoot . '/config/postUpdate', "SelfUpdate::postUpdate() DONE");
        return true;
    }

    public function postUpdate()
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
