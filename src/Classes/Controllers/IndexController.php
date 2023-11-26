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

namespace RobinTheHood\ModifiedModuleLoaderClient\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use RobinTheHood\ModifiedModuleLoaderClient\AccessFileCreator;
use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\ModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\RemoteModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFilter;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleSorter;
use RobinTheHood\ModifiedModuleLoaderClient\Category;
use RobinTheHood\ModifiedModuleLoaderClient\SendMail;
use RobinTheHood\ModifiedModuleLoaderClient\Config;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\DependencyException;
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\DependencyManager;
use RobinTheHood\ModifiedModuleLoaderClient\MmlcVersionInfoLoader;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleInstaller;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleStatus;
use RobinTheHood\ModifiedModuleLoaderClient\Notification;
use RobinTheHood\ModifiedModuleLoaderClient\SelfUpdater;
use RuntimeException;

class IndexController extends Controller
{
    private const REQUIRED_PHP_VERSION = '7.4.0';

    /** @var ModuleInstaller */
    private $moduleInstaller;

    /** @var ModuleFilter */
    private $moduleFilter;

    public function __construct(ServerRequestInterface $serverRequest, array $session = [])
    {
        parent::__construct($serverRequest, $session);

        $this->moduleInstaller = ModuleInstaller::createFromConfig();
        $this->moduleFilter = ModuleFilter::createFromConfig();
    }

    public function invoke()
    {
        $this->invokeDefault();

        switch ($this->getAction()) {
            case 'moduleInfo':
                return $this->invokeModuleInfo();
            case 'lazyModuleInfo':
                return $this->invokeLazyModuleInfo();
            case 'lazyModuleUpdateCount':
                return $this->invokeLazyModuleUpdateCount();
            case 'lazyModuleChangeCount':
                return $this->invokeLazyModuleChangeCount();
            case 'lazySystemUpdateCount':
                return $this->invokeLazySystemUpdateCount();
            // case 'install':
            //     return $this->invokeInstall();
            // case 'update':
            //     return $this->invokeUpdate();
            // case 'uninstall':
            //     return $this->invokeUninstall();
            // case 'loadRemoteModule':
            //     return $this->invokeLoadRemoteModule();
            // case 'loadAndInstall':
            //     return $this->invokeLoadAndInstall();
            // case 'unloadLocalModule':
            //     return $this->invokeUnloadLocalModule();
            // case 'revertChanges':
            //     return $this->invokeRevertChanges();
            // case 'signIn':
            //     return $this->invokeSignIn();
            // case 'signOut':
            //     return $this->invokeSignOut();
            // case 'selfUpdate':
            //     return $this->invokeSelfUpdate();
            // case 'reportIssue':
            //     return $this->invokeReportIssue();
            // case 'support':
            //     return $this->invokeSupport();
            // case 'settings':
            //     return $this->invokeSettings();
            default:
                return $this->invokeIndex();
        }
    }

    public function invokeDefault()
    {
        $accessFileCreator = new AccessFileCreator();
        $accessFileCreator->renewAccessFiles();

        if (!ini_get('allow_url_fopen')) {
            Notification::pushFlashMessage([
                'text' =>
                    'Warnung: Keine Verbindung zum Server.
                    <strong>allow_url_fopen</strong> ist in der php.ini deaktiviert.',
                'type' => 'warning'
            ]);
        }

        if (version_compare(PHP_VERSION, self::REQUIRED_PHP_VERSION, '<')) {
            Notification::pushFlashMessage([
                'text' =>
                    'Warnung: Die PHP Version ' . PHP_VERSION . ' wird nicht unterstützt. Der MMLC benötigt '
                    . self::REQUIRED_PHP_VERSION . ' oder höher.',
                'type' => 'warning'
            ]);
        }

        if (!Config::getAccessToken()) {
            $accessToken = md5($accessToken = uniqid('mmlcAccessToken', true));
            Config::setAccessToken($accessToken);
        }
    }

    public function invokeSignIn()
    {
        if ($this->isPostRequest()) {
            $error = '';

            $parsedBody = $this->serverRequest->getParsedBody();
            $username = $parsedBody['username'] ?? '';
            $password = $parsedBody['password'] ?? '';

            if ($username != Config::getUsername()) {
                $error = 'Unbekannter Benutzername';
            } elseif (!password_verify($password, Config::getPassword() ?? '')) {
                $error = 'Falsches passwort';
            }

            if (!$error) {
                $_SESSION['accessRight'] = true;
                return $this->redirect('/');
            } else {
                $_SESSION['accessRight'] = false;
            }
        }

        return $this->render('SignIn');
    }

    public function invokeSignOut()
    {
        $_SESSION['accessRight'] = false;
        return $this->redirect('/?action=signIn');
    }

    public function invokeSelfUpdate()
    {
        if ($accessRedirect = $this->checkAccessRight()) {
            return $accessRedirect;
        }

        $gitBranch = $this->getCurrentGitBranch(App::getRoot() . '/.git');

        if ($gitBranch) {
            Notification::pushFlashMessage([
                'text' =>
                    'Der MMLC wurde über Git installiert.<br>
                    🔀 Branch: <strong>' . $gitBranch . '</strong><br>
                    Bitte führe die Aktualisierung des MMLC über Git durch. Beachte, dass ein Update über den MMLC
                    möglicherweise zu Fehlern führen kann.',
                'type' => 'warning'
            ]);
        }

        // Nächste mögliche MMLC Version ermittlen
        $latest = Config::getSelfUpdate() == 'latest';
        $installedMmlcVersionString = App::getMmlcVersion();
        $selfUpdater = new SelfUpdater(MmlcVersionInfoLoader::createLoader());
        $mmlcVersionInfo = $selfUpdater->getNextMmlcVersionInfo($installedMmlcVersionString, $latest);

        // Update durchführen, wenn ausgewählt und vorhanden
        $queryParams = $this->serverRequest->getQueryParams();
        $installVersion = $queryParams['install'] ?? '';
        if ($mmlcVersionInfo && $mmlcVersionInfo->version === $installVersion) {
            $selfUpdater->update($mmlcVersionInfo);
            return $this->redirect('/?action=selfUpdate');
        }

        // Postupdate ausführen. Kann immer aufgerufen werden. Die Methode entscheidet selbst,
        // ob etwas getan werden muss oder nicht.
        $postUpdateExecuted = $selfUpdater->postUpdate();

        // Wenn ein Postupdate durchgeführt wurde, die Seite noch einmal automatisch neu laden.
        if ($postUpdateExecuted) {
            return $this->redirect('/?action=selfUpdate');
        }

        return $this->render('SelfUpdate', [
            'mmlcVersionInfo' => $mmlcVersionInfo,
            'installedVersionString' => $installedMmlcVersionString,
            'serverName' => $_SERVER['SERVER_NAME'] ?? 'unknown Server Name'
        ]);
    }

    public function invokeIndex()
    {
        if ($accessRedirect = $this->checkAccessRight()) {
            return $accessRedirect;
        }

        $moduleLoader = ModuleLoader::createFromConfig();
        $modules = $moduleLoader->loadAllVersionsWithLatestRemote();
        $modules = $this->moduleFilter->filterNewestOrInstalledVersion($modules);

        $heading = 'Alle Module';

        $queryParams = $this->serverRequest->getQueryParams();
        $filterModules = $queryParams['filterModules'] ?? '';

        if ($filterModules == 'loaded') {
            $modules = $this->moduleFilter->filterLoaded($modules);
            $heading = 'Geladene Module';
        } elseif ($filterModules == 'installed') {
            $modules = $this->moduleFilter->filterInstalled($modules);
            $heading = 'Installierte Module';
        } elseif ($filterModules == 'updatable') {
            $modules = $this->moduleFilter->filterUpdatable($modules);
            $heading = 'Aktualisierbare Module';
        } elseif ($filterModules == 'changed') {
            $modules = $this->moduleFilter->filterRepairable($modules);
            $heading = 'Geänderte Module';
        } elseif ($filterModules == 'notloaded') {
            $modules = $this->moduleFilter->filterNotLoaded($modules);
            $heading = 'Nicht geladene Module';
        }

        $modules = ModuleSorter::sortByArchiveName($modules);
        $groupedModules = Category::groupByCategory($modules);

        return $this->render('ModuleListing', [
            'heading' => $heading,
            'modules' => $modules,
            'groupedModules' => $groupedModules
        ]);
    }

    public function invokeModuleInfo()
    {
        if ($accessRedirect = $this->checkAccessRight()) {
            return $accessRedirect;
        }

        $queryParams = $this->serverRequest->getQueryParams();
        $archiveName = $queryParams['archiveName'] ?? '';
        $version = $queryParams['version'] ?? '';

        if ($version) {
            $moduleLoader = ModuleLoader::createFromConfig();
            $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);
        } else {
            $moduleLoader = ModuleLoader::createFromConfig();
            $modules = $moduleLoader->loadAllVersionsByArchiveNameWithLatestRemote($archiveName);
            $module = $this->moduleFilter->getLatestVersion($modules);
        }

        if (!$module) {
            $this->addModuleNotFoundNotification($archiveName, $version);
            return $this->redirect('/');
        }

        if ($error = ModuleStatus::hasValidRequire($module)) {
            Notification::pushFlashMessage([
                'type' => 'error',
                'text' => 'Error in require in moduleinfo.json of '
                    . $module->getArchiveName() . ' ' . $module->getVersion() . ' - ' . $error
            ]);
        }

        if ($module->isInstalled()) {
            $dependencyManger = DependencyManager::createFromConfig();
            $missingDependencies = $dependencyManger->getMissingDependencies($module);
            if ($missingDependencies) {
                $string = '';
                foreach ($missingDependencies as $archiveName => $version) {
                    $string .= '▶️ ' . $archiveName . ' ' . $version . "\n";
                }

                Notification::pushFlashMessage([
                    'type' => 'warning',
                    'text' =>
                        'Einige Abhängigkeiten sind nicht installiert. Das Fehlen von Abhängigkeiten kann zu Fehlern
                        bei der Ausführung des Moduls führen. Installiere die folgenden fehlenden Abhänigkeiten:<br>'
                        . nl2br($string)
                ]);
            }
        }

        return $this->render('ModuleInfo', [
            'module' => $module
        ]);
    }

    public function invokeLazyModuleInfo()
    {
        if ($accessRedirect = $this->checkAccessRight()) {
            return $accessRedirect;
        }

        $queryParams = $this->serverRequest->getQueryParams();
        $archiveName = $queryParams['archiveName'] ?? '';
        $version = $queryParams['version'] ?? '';
        $data = $queryParams['data'] ?? '';

        $moduleLoader = ModuleLoader::createFromConfig();
        $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            return ['content' => ''];
        }

        $description = $module->getDescriptionMd() !== '' ? $module->getDescriptionMd() : $module->getDescription();

        if ($data == 'installationMd') {
            return ['content' => $module->getInstallationMd()];
        } elseif ($data == 'usageMd') {
            return ['content' => $module->getUsageMd()];
        } elseif ($data == 'changelogMd') {
            return ['content' => $module->getChangeLogMd()];
        } elseif ($data == 'readmeMd') {
            return ['content' => $module->getReadmeMd()];
        } elseif ($data == 'descriptionMd') {
            return ['content' => $description];
        }
    }

    public function invokeLazyModuleUpdateCount()
    {
        if ($accessRedirect = $this->checkAccessRight()) {
            return $accessRedirect;
        }

        $value = $this->calcModuleUpdateCount();
        if ($value) {
            return ['content' => $value];
        }
    }

    public function invokeLazyModuleChangeCount()
    {
        if ($accessRedirect = $this->checkAccessRight()) {
            return $accessRedirect;
        }

        $value = $this->calcModuleChangeCount();
        if ($value) {
            return ['content' => $value];
        }
    }

    public function invokeLazySystemUpdateCount()
    {
        if ($accessRedirect = $this->checkAccessRight()) {
            return $accessRedirect;
        }

        $value = $this->calcSystemUpdateCount();
        if ($value) {
            return ['content' => $value];
        }
    }

    public function invokeInstall()
    {
        if ($accessRedirect = $this->checkAccessRight()) {
            return $accessRedirect;
        }

        $queryParams = $this->serverRequest->getQueryParams();
        $archiveName = $queryParams['archiveName'] ?? '';
        $version = $queryParams['version'] ?? '';

        $moduleLoader = LocalModuleLoader::createFromConfig();
        $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            $this->addModuleNotFoundNotification($archiveName, $version);
            return $this->redirect('/');
        }

        try {
            $this->moduleInstaller->installWithDependencies($module);
        } catch (DependencyException $e) {
            Notification::pushFlashMessage([
                'text' => $e->getMessage(),
                'type' => 'error'
            ]);
        } catch (RuntimeException $e) {
            Notification::pushFlashMessage([
                'text' => $e->getMessage(),
                'type' => 'error'
            ]);
        }

        return $this->redirectRef($archiveName, $module->getVersion());
    }

    private function invokeRevertChanges()
    {
        if ($accessRedirect = $this->checkAccessRight()) {
            return $accessRedirect;
        }

        $queryParams = $this->serverRequest->getQueryParams();
        $archiveName = $queryParams['archiveName'] ?? '';
        $version = $queryParams['version'] ?? '';

        $moduleLoader = LocalModuleLoader::createFromConfig();
        $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            $this->addModuleNotFoundNotification($archiveName, $version);
            return $this->redirect('/');
        }

        try {
            $this->moduleInstaller->revertChanges($module);
        } catch (DependencyException $e) {
            Notification::pushFlashMessage([
                'text' => $e->getMessage(),
                'type' => 'error'
            ]);
        } catch (RuntimeException $e) {
            Notification::pushFlashMessage([
                'text' => $e->getMessage(),
                'type' => 'error'
            ]);
        }

        return $this->redirectRef($archiveName, $module->getVersion());
    }

    public function invokeUninstall()
    {
        if ($accessRedirect = $this->checkAccessRight()) {
            return $accessRedirect;
        }

        $queryParams = $this->serverRequest->getQueryParams();
        $archiveName = $queryParams['archiveName'] ?? '';
        $version = $queryParams['version'] ?? '';

        $moduleLoader = LocalModuleLoader::createFromConfig();
        $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            $this->addModuleNotFoundNotification($archiveName, $version);
            return $this->redirect('/');
        }

        try {
            $this->moduleInstaller->uninstall($module);
        } catch (DependencyException $e) {
            Notification::pushFlashMessage([
                'text' => $e->getMessage(),
                'type' => 'error'
            ]);
        } catch (RuntimeException $e) {
            Notification::pushFlashMessage([
                'text' => $e->getMessage(),
                'type' => 'error'
            ]);
        }

        return $this->redirectRef($archiveName, $module->getVersion());
    }

    public function invokeUpdate()
    {
        if ($accessRedirect = $this->checkAccessRight()) {
            return $accessRedirect;
        }

        $queryParams = $this->serverRequest->getQueryParams();
        $archiveName = $queryParams['archiveName'] ?? '';
        $version = $queryParams['version'] ?? '';

        $moduleLoader = LocalModuleLoader::createFromConfig();
        $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            $this->addModuleNotFoundNotification($archiveName, $version);
            return $this->redirect('/');
        }

        $newModule = $module;

        try {
            $newModule = $this->moduleInstaller->updateWithDependencies($module);
        } catch (DependencyException $e) {
            Notification::pushFlashMessage([
                'text' => $e->getMessage(),
                'type' => 'error'
            ]);
        } catch (RuntimeException $e) {
            Notification::pushFlashMessage([
                'text' => $e->getMessage(),
                'type' => 'error'
            ]);
        }

        if (!$newModule) {
            $newestModule = $module->getNewestVersion();
            $this->addModuleNotFoundNotification($archiveName, $newestModule->getVersion());
            return $this->redirect('/');
        }

        return $this->redirectRef($archiveName, $newModule->getVersion());
    }

    public function invokeLoadRemoteModule()
    {
        if ($accessRedirect = $this->checkAccessRight()) {
            return $accessRedirect;
        }

        $queryParams = $this->serverRequest->getQueryParams();
        $archiveName = $queryParams['archiveName'] ?? '';
        $version = $queryParams['version'] ?? '';

        $moduleLoader = RemoteModuleLoader::create();
        $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            $this->addModuleNotFoundNotification($archiveName, $version);
            return $this->redirect('/');
        }

        if (!$this->moduleInstaller->pull($module)) {
            Notification::pushFlashMessage([
                'text' => "Fehler: Das Module <strong>$archiveName - $version</strong> konnte nicht geladen werden.",
                'type' => 'error'
            ]);
        }

        return $this->redirectRef($archiveName, $module->getVersion());
    }

    public function invokeLoadAndInstall()
    {
        if ($accessRedirect = $this->checkAccessRight()) {
            return $accessRedirect;
        }

        $queryParams = $this->serverRequest->getQueryParams();
        $archiveName = $queryParams['archiveName'] ?? '';
        $version = $queryParams['version'] ?? '';

        $moduleLoader = RemoteModuleLoader::create();
        $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            $this->addModuleNotFoundNotification($archiveName, $version);
            return $this->redirect('/');
        }

        if (!$this->moduleInstaller->pull($module)) {
            Notification::pushFlashMessage([
                'text' => "Fehler: Das Module <strong>$archiveName - $version</strong> konnte nicht geladen werden.",
                'type' => 'error'
            ]);
            return $this->redirect('/');
        }

        $moduleLoader = LocalModuleLoader::createFromConfig();
        $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            $this->addModuleNotFoundNotification($archiveName, $version);
            return $this->redirect('/');
        }

        try {
            $this->moduleInstaller->installWithDependencies($module);
        } catch (DependencyException $e) {
            Notification::pushFlashMessage([
                'text' => $e->getMessage(),
                'type' => 'error'
            ]);
        } catch (RuntimeException $e) {
            Notification::pushFlashMessage([
                'text' => $e->getMessage(),
                'type' => 'error'
            ]);
        }

        return $this->redirectRef($archiveName, $module->getVersion());
    }

    public function invokeUnloadLocalModule()
    {
        if ($accessRedirect = $this->checkAccessRight()) {
            return $accessRedirect;
        }

        $queryParams = $this->serverRequest->getQueryParams();
        $archiveName = $queryParams['archiveName'] ?? '';
        $version = $queryParams['version'] ?? '';

        $moduleLoader = LocalModuleLoader::createFromConfig();
        $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            $this->addModuleNotFoundNotification($archiveName, $version);
            return $this->redirect('/');
        }

        try {
            $this->moduleInstaller->delete($module);
        } catch (DependencyException $e) {
            Notification::pushFlashMessage([
                'text' => $e->getMessage(),
                'type' => 'error'
            ]);
        } catch (RuntimeException $e) {
            Notification::pushFlashMessage([
                'text' => $e->getMessage(),
                'type' => 'error'
            ]);
        }

        return $this->redirect('/');
    }

    public function invokeReportIssue()
    {
        if ($accessRedirect = $this->checkAccessRight()) {
            return $accessRedirect;
        }

        $parsedBody = $this->serverRequest->getParsedBody();
        if (isset($parsedBody['send_mail'])) {
            SendMail::sendIssue();
        }

        return $this->render('ReportIssue');
    }

    public function invokeSupport()
    {
        if ($accessRedirect = $this->checkAccessRight()) {
            return $accessRedirect;
        }

        return $this->render('Support');
    }

    public function invokeSettings()
    {
        if ($accessRedirect = $this->checkAccessRight()) {
            return $accessRedirect;
        }

        /**
         * Save submitted form input to config.
         */
        if ($this->isPostRequest()) {
            $parsedBody = $this->serverRequest->getParsedBody();

            if (isset($parsedBody['adminDir'])) {
                Config::setAdminDir($parsedBody['adminDir']);
            }

            if (isset($parsedBody['username'])) {
                Config::setUsername($parsedBody['username']);
            }

            /**
             * Don't overwrite the password
             * if the user doesn't want to change it.
             */
            if (!empty($parsedBody['password'])) {
                Config::setPassword($parsedBody['password']);
            }

            if (isset($parsedBody['accessToken'])) {
                Config::setAccessToken($parsedBody['accessToken']);
            }

            if (isset($parsedBody['shopRoot'])) {
                Config::setShopRoot($parsedBody['shopRoot']);
            }

            if (isset($parsedBody['modulesLocalDir'])) {
                Config::setModulesLocalDir($parsedBody['modulesLocalDir']);
            }

            if (isset($parsedBody['logging'])) {
                Config::setLogging($parsedBody['logging']);
            }

            if (isset($parsedBody['installMode'])) {
                Config::setInstallMode($parsedBody['installMode']);
            }

            if (isset($parsedBody['dependencyMode'])) {
                Config::setDependencyMode($parsedBody['dependencyMode']);
            }

            if (isset($parsedBody['exceptionMonitorDomain'])) {
                Config::setExceptionMonitorDomain($parsedBody['exceptionMonitorDomain']);
            }

            Notification::pushFlashMessage([
                'text' => 'Einstellungen erfolgreich gespeichert.',
                'type' => 'success'
            ]);

            $queryParams = $this->serverRequest->getQueryParams();
            $section = $queryParams['section'] ?? '';

            return $this->redirect('/?action=settings&section=' . $section);
        }

        return $this->render('Settings');
    }

    public function calcModuleUpdateCount()
    {
        $moduleLoader = LocalModuleLoader::createFromConfig();
        $modules = $moduleLoader->loadAllVersions();
        $modules = $this->moduleFilter->filterInstalled($modules);
        return count($this->moduleFilter->filterUpdatable($modules));
    }

    public function calcModuleChangeCount()
    {
        $moduleLoader = LocalModuleLoader::createFromConfig();
        $modules = $moduleLoader->loadAllVersions();
        return count($this->moduleFilter->filterRepairable($modules));
    }

    public function calcSystemUpdateCount()
    {
        $latest = Config::getSelfUpdate() == 'latest';
        $installedMmlcVersionString = App::getMmlcVersion();

        $selfUpdater = new SelfUpdater(MmlcVersionInfoLoader::createLoader());
        $checkUpdate = $selfUpdater->updateAvailable($installedMmlcVersionString, $latest);
        if ($checkUpdate) {
            return 1;
        }
        return 0;
    }

    public function checkAccessRight()
    {
        if (empty($_SESSION['accessRight']) || $_SESSION['accessRight'] !== true) {
            return $this->redirect('/?action=signIn');
        }
    }

    private function redirect($url)
    {
        return [
            'content' => '',
            'redirect' => $url
        ];
    }

    public function redirectRef($archiveName, $version = '')
    {
        $queryParams = $this->serverRequest->getQueryParams();
        $ref = $queryParams['ref'] ?? '';

        if ($ref == 'moduleInfo') {
            $url = '/?action=moduleInfo&archiveName=' . $archiveName;
            if ($version) {
                $url .= '&version=' . $version;
            }
        } else {
            $url = '/';
        }

        return $this->redirect($url);
    }

    private function addModuleNotFoundNotification($archiveName, $version = '')
    {
        Notification::pushFlashMessage([
            'text' => "Fehler: Das Module <strong>$archiveName - $version</strong> wurde nicht gefunden.",
            'type' => 'error'
        ]);
    }

    private function getCurrentGitBranch(string $gitPath): ?string
    {
        if (!is_dir($gitPath)) {
            return null;
        }

        $os = strtoupper(substr(PHP_OS, 0, 3));
        $command = '';

        switch ($os) {
            case 'WIN':
                $command = 'cd /d "' . $gitPath . '" & git symbolic-ref --short HEAD 2>NUL';
                break;
            case 'LIN':
            case 'DAR':
                $command = 'cd "' . $gitPath . '" && git symbolic-ref --short HEAD 2>/dev/null';
                break;
            default:
                return 'unkown branch';
        }

        $output = trim('' . shell_exec($command));

        if (empty($output)) {
            return null;
        }

        return $output;
    }
}
