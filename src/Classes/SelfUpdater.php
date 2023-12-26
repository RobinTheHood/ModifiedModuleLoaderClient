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
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;
use RobinTheHood\ModifiedModuleLoaderClient\Api\V1\HttpRequest;
use RuntimeException;
use Throwable;

class SelfUpdater
{
    /** @var string */
    private $appRoot = '';

    /** @var string */
    private $remoteUpdateServer;

    /** @var Comparator */
    private $comparator;

    /** @var MmlcVersionInfoLoader */
    private $mmlcVersionInfoLoader;

    /**
     * Während der Installtion werden Dateien und damit auch die Pfade von Klassen verschoben.
     * Wird eine Klasse geladen, nachdem die Datei verschoben wurde, kann der PHP Classloader die Klasse
     * nicht finden und ein Fehler tritt auf. Aus diesem Grund müssen alle benötigten Daten und Klassen in den Speicher
     * geladen werden, bevor die Dateien verschoben werden.
     */
    public function __construct(MmlcVersionInfoLoader $mmlcVersionInfoLoader)
    {
        $this->appRoot = App::getRoot();
        $this->mmlcVersionInfoLoader = $mmlcVersionInfoLoader;
        $this->remoteUpdateServer = $this->getRomteUpdateServer();
        $this->comparator = Comparator::create(Comparator::CARET_MODE_STRICT);
    }

    /**
     * Gibt true zurück wenn eine neue MMLC Version verfügbar ist.
     */
    public function updateAvailable(string $installedMmlcVersionString, bool $latest): bool
    {
        if ($this->getNextMmlcVersionInfo($installedMmlcVersionString, $latest)) {
            return true;
        }
        return false;
    }

    /**
     * Gibt die nächst möglichste MmlcVersionInfo zurück, falls eine neue MMLC Version verfügbar ist.
     */
    public function getNextMmlcVersionInfo(string $installedMmlcVersionString, bool $latest): ?MmlcVersionInfo
    {
        $mmlcVersionInfo = $this->mmlcVersionInfoLoader->getNextNewest($installedMmlcVersionString, $latest);
        if (!$mmlcVersionInfo) {
            return null;
        }

        if (!$this->comparator->greaterThan($mmlcVersionInfo->version, $installedMmlcVersionString)) {
            return null;
        }

        return $mmlcVersionInfo;
    }

    public function update(MmlcVersionInfo $mmlcVersionInfo): void
    {
        if (!$mmlcVersionInfo->fileName) {
            return;
        }

        if (!$mmlcVersionInfo->version) {
            return;
        }

        $this->createRestore($mmlcVersionInfo);
        $this->download($mmlcVersionInfo);
        $this->untar($mmlcVersionInfo);
        $this->verifyUntar($mmlcVersionInfo);

        $check = $this->systemCheck($mmlcVersionInfo);

        if ($check) {
            $this->backup($mmlcVersionInfo);
            $this->install($mmlcVersionInfo);
            $this->setupConfig($mmlcVersionInfo);
            $this->setupVersion($mmlcVersionInfo);
            $this->verifyUpdate($mmlcVersionInfo);
        }

        $this->remove($mmlcVersionInfo);
        $this->removeRestore($mmlcVersionInfo);

        opcache_reset();
    }

    public function postUpdate(): bool
    {
        if (file_exists($this->appRoot . '/config/postUpdate')) {
            return false;
        }

        $this->postUpdateSteps();
        system('rm -rf ' . $this->appRoot . '/backup');

        file_put_contents($this->appRoot . '/config/postUpdate', "SelfUpdate::postUpdate() DONE");

        return true;
    }

    private function download(MmlcVersionInfo $mmlcVersionInfo): bool
    {
        $remoteAddress = $this->remoteUpdateServer . $mmlcVersionInfo->fileName;

        $httpRequest = new HttpRequest();
        $tarBall = $httpRequest->sendGetRequest($remoteAddress);

        if (!$tarBall) {
            $this->showSoftError("Can not download file: $remoteAddress");
            return false;
        }

        file_put_contents($this->appRoot . '/' . $mmlcVersionInfo->fileName, $tarBall);

        return true;
    }

    private function backup(MmlcVersionInfo $mmlcVersionInfo): bool
    {
        $srcPath = $this->appRoot;
        $destPath = $this->appRoot . '/backup';

        if (!file_exists($destPath)) {
            mkdir($destPath);
        }

        if (!file_exists($destPath)) {
            $this->showSoftError("Can not create directory $destPath");
            return false;
        }

        $exclude = [
            '/ModifiedModuleLoaderClient',
            '/Archives',
            '/Modules',
            '/backup',
            '/restore.php',
            '/' . $mmlcVersionInfo->fileName
        ];

        $files = FileHelper::scanDir($srcPath, FileHelper::FILES_AND_DIRS, true);
        FileHelper::moveFilesTo($files, $srcPath, $destPath, $exclude);

        return true;
    }

    private function createRestore(MmlcVersionInfo $mmlcVersionInfo): bool
    {
        $rootPath = $this->appRoot;

        $restoreTemplateFilePath = $rootPath . '/src/Templates/restore.php.tmpl';
        $restoreFilePath = $rootPath . '/restore.php';

        if (!file_exists($restoreTemplateFilePath)) {
            $this->showSoftError("Can not find file: $restoreTemplateFilePath");
            return false;
        }

        copy($restoreTemplateFilePath, $restoreFilePath);

        if (!file_exists($restoreFilePath)) {
            $this->showSoftError("Can not create the automatic restore file at: $restoreFilePath");
            return false;
        }

        return true;
    }

    private function removeRestore(MmlcVersionInfo $mmlcVersionInfo)
    {
        $rootPath = $this->appRoot;
        $restoreFilePath = $rootPath . '/restore.php';

        system('rm -rf ' . $restoreFilePath);
    }

    private function untar(MmlcVersionInfo $mmlcVersionInfo): void
    {
        $tarFilePath = $this->appRoot . '/' . $mmlcVersionInfo->fileName;

        $tarBall = new \PharData($mmlcVersionInfo->fileName);
        $tarBall->extractTo($this->appRoot, null, true);

        system('rm -rf ' . $tarFilePath);
    }

    private function install(MmlcVersionInfo $mmlcVersionInfo): void
    {
        $srcPath = $this->appRoot . '/ModifiedModuleLoaderClient';
        $destPath = $this->appRoot;

        if (!is_dir($srcPath)) {
            $this->showError("Can not install update because, can not find: <br>\n $srcPath");
            return;
        }

        $files = FileHelper::scanDir($srcPath, FileHelper::FILES_AND_DIRS, true);
        FileHelper::moveFilesTo($files, $srcPath, $destPath);
    }

    private function remove(MmlcVersionInfo $mmlcVersionInfo): void
    {
        $srcPath = $this->appRoot . '/ModifiedModuleLoaderClient';
        system('rm -rf ' . $srcPath);
    }

    private function setupConfig(MmlcVersionInfo $mmlcVersionInfo): void
    {
        @unlink($this->appRoot . '/config/config.php');
        @copy($this->appRoot . '/backup/config/config.php', $this->appRoot . '/config/config.php');
    }

    private function setupVersion(MmlcVersionInfo $mmlcVersionInfo): void
    {
        $versionFilePath = $this->appRoot . '/config/version.json';
        if (!file_exists($versionFilePath)) {
            file_put_contents(
                $this->appRoot . '/config/version.json',
                '{"version": "' . $mmlcVersionInfo->version . '"}'
            );
        }
    }

    private function verifyUntar(MmlcVersionInfo $mmlcVersionInfo)
    {
        $checkPaths = [
            $this->appRoot . '/ModifiedModuleLoaderClient/index.php',
            $this->appRoot . '/ModifiedModuleLoaderClient/src',
            $this->appRoot . '/ModifiedModuleLoaderClient/vendor',
            $this->appRoot . '/ModifiedModuleLoaderClient/config'
        ];

        $missingPaths = [];
        foreach ($checkPaths as $path) {
            if (!file_exists($path)) {
                $missingPaths[] = $path;
            }
        }

        if ($missingPaths) {
            $missingPathsString = implode("<br>\n", $missingPaths);
            $this->showError("verify untar - can not find: <br>\n $missingPathsString");
            return false;
        }

        return true;
    }

    private function verifyUpdate(MmlcVersionInfo $mmlcVersionInfo): bool
    {
        $checkPaths = [
            $this->appRoot . '/index.php',
            $this->appRoot . '/src',
            $this->appRoot . '/vendor',
            $this->appRoot . '/config',
            $this->appRoot . '/config/config.php',
        ];

        $missingPaths = [];
        foreach ($checkPaths as $path) {
            if (!file_exists($path)) {
                $missingPaths[] = $path;
            }
        }

        if ($missingPaths) {
            $missingPathsString = implode("<br>\n", $missingPaths);
            $this->showError("Verify update - can not find: <br>\n $missingPathsString");
            return false;
        }

        return true;
    }

    private function systemCheck(MmlcVersionInfo $mmlcVersionInfo): bool
    {
        try {
            $systemCheck = $this->getSystemCheckObj();
            $check = $systemCheck->check();
            if ($check['result'] === 'passed') {
                return true;
            } else {
                Notification::pushFlashMessage([
                    'text' => "Update canceled - Can not update MMLC. Not all system requirements are met.<br>\n"
                    . json_encode($check['checks'], JSON_PRETTY_PRINT),
                    'type' => Notification::TYPE_ERROR
                ]);
            }
        } catch (RuntimeException $e) {
            Notification::pushFlashMessage([
                'text' => "Update canceled - " . $e->getMessage(),
                'type' => Notification::TYPE_ERROR
            ]);
        }

        return false;
    }

    private function getSystemCheckObj()
    {
        $systemCheckFilePath = $this->appRoot . '/ModifiedModuleLoaderClient/src/Classes/SystemCheck.php';
        // $systemCheckFilePath = $this->appRoot . '/src/Classes/SystemCheck.php'; // Für Testzwecke

        if (!file_exists($systemCheckFilePath)) {
            throw new RuntimeException("Can not find file $systemCheckFilePath in downloaded .tar file");
        }

        try {
            $fileContent = file_get_contents($systemCheckFilePath);
            $fileContent = str_replace('class SystemCheck', 'class SystemCheckNew', $fileContent);
            $fileContent = str_replace('<?php', '', $fileContent);

            eval($fileContent);

            $classSystemCheckNew = 'RobinTheHood\ModifiedModuleLoaderClient\SystemCheckNew';
            return new $classSystemCheckNew();
        } catch (Throwable $t) {
            throw new RuntimeException("Can not load file $systemCheckFilePath");
        }
    }

    private function postUpdateSteps(): void
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
        // Wenn die config/version.json Datei fehlt, gibt es 2 Möglichkeiten diese
        // zu erzeugen.
        $dest = $this->appRoot . '/config/version.json';

        if (!file_exists($dest) && file_exists($this->appRoot . '/ModifiedModuleLoaderClient/config/version.json')) {
            rename($this->appRoot . '/ModifiedModuleLoaderClient/config/version.json', $dest);
        }

        if (!file_exists($dest) && file_exists($this->appRoot . '/version.json')) {
            rename($this->appRoot . 'version.json', $dest);
        }
    }

    private function getRomteUpdateServer(): string
    {
        $remoteAddress = Config::getRemoteAddress() ?? '';

        if (empty(Config::getRemoteAddress())) {
            throw new \RuntimeException('Unable to connect. RemoteAddress is empty or not set.');
        }

        $remoteUpdateServer = str_replace('/api.php', '/Downloads/', $remoteAddress);
        return $remoteUpdateServer;
    }

    private function showSoftError(string $message): void
    {
        $errorMessage = ""
            . "<h1>⚠️ ATTENTION: DO NOT RELOAD THIS PAGE</h1>\n"
            . "Because this message will disappear and probably no longer be displayed after a reload "
            . "or leads to further errors. You can close the window if you don't want to read the message anymore."
            . "<h2>The MMLC update was interrupted</h2>\n"
            . "<h3>❌ ERROR</h3>"
            . "$message<br>\n"
            . "<h3>🛠️ WHAT CAN YOU DO</h3>\n"
            . "You can go back with the following url: "
            . "<a href=\"?action=selfUpdate\">Back to the MMLC System Page</a><br>\n";

        $css = "
            <style>
                .message-frame {
                    max-width: 800px;
                    margin: 50px auto;
                    padding: 40px;
                    font-family: Arial;
                    border-radius: 5px;
                    box-shadow: 0 0 12px 0 rgba(0, 0, 0, 0.25);
                    line-height: 24px;
                    font-size: 16px;
                }

                .message-frame li {
                    margin-bottom: 20px
                }
            </style>
        ";

        $errorMessage = ''
            . $css
            . "\n"
            . '<div class="message-frame">'
                . $errorMessage
            . '</div>';

        die($errorMessage);
    }

    private function showError(string $message): void
    {
        $rootPath = $this->appRoot;
        $backupPath = $this->appRoot . '/backup';

        $errorMessage = ""
            . "<h1>⚠️ ATTENTION: DO NOT RELOAD THIS PAGE</h1>\n"
            . "Because this message will disappear and probably no longer be displayed after a reload "
            . "or leads to further errors. You can close the window if you don't want to read the message anymore."
            . "<h2>The MMLC update was interrupted</h2>\n"
            . "Your MMLC is now in an unsave and unusable state.<br>\n"
            . "<h3>❌ ERROR</h3>"
            . "$message<br>\n"
            . "<h3>🛠️ WHAT CAN YOU DO</h3>\n"
            . "<ul>\n"
            . "<li>Variant A - Try the following restore link, it will open in a new window. "
            . "<a href=\"restore.php\" target=\"?action=_blank\">To the MMLC restore script in a new window</a></li>\n"
            . "<li>Variant B - Try to restore you MMLC by moving all files/directories from "
            . "$backupPath to $rootPath<br>\n"
            . "and delete directory $rootPath/ModifiedModuleLoaderClient if exists</li>\n"
            . "<li>Variant C - Go to module-loader.de and load the installer to reinstall the MMLC. "
            . "The installer will try to keep your settings and module data.</li>\n"
            . "</ul>\n";

        $css = "
            <style>
                .message-frame {
                    max-width: 800px;
                    margin: 50px auto;
                    padding: 40px;
                    font-family: Arial;
                    border-radius: 5px;
                    box-shadow: 0 0 12px 0 rgba(0, 0, 0, 0.25);
                    line-height: 24px;
                    font-size: 16px;
                }

                .message-frame li {
                    margin-bottom: 20px
                }
            </style>
        ";

        $errorMessage = ''
            . $css
            . "\n"
            . '<div class="message-frame">'
                . $errorMessage
            . '</div>';

        die($errorMessage);
    }
}
