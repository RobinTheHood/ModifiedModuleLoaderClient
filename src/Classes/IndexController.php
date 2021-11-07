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

use RobinTheHood\ModifiedModuleLoaderClient\Loader\ModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\LocalModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\RemoteModuleLoader;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;
use RobinTheHood\ModifiedModuleLoaderClient\Redirect;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFilter;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleSorter;
use RobinTheHood\ModifiedModuleLoaderClient\Category;
use RobinTheHood\ModifiedModuleLoaderClient\SendMail;
use RobinTheHood\ModifiedModuleLoaderClient\Config;

class IndexController extends Controller
{
    private const REQUIRED_PHP_VERSION = '7.1.12';

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
            case 'install':
                return $this->invokeInstall();
            case 'update':
                return $this->invokeUpdate();
            case 'uninstall':
                return $this->invokeUninstall();
            case 'loadRemoteModule':
                return $this->invokeLoadRemoteModule();
            case 'loadAndInstall':
                return $this->invokeLoadAndInstall();
            case 'unloadLocalModule':
                return $this->invokeUnloadLocalModule();
            case 'revertChanges':
                return $this->invokeRevertChanges();
            case 'signIn':
                return $this->invokeSignIn();
            case 'signOut':
                return $this->invokeSignOut();
            case 'selfUpdate':
                return $this->invokeSelfUpdate();
            case 'reportIssue':
                return $this->invokeReportIssue();
            case 'support':
                return $this->invokeSupport();
            case 'settings':
                return $this->invokeSettings();
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
                'text' => 'Warnung: Keine Verbindung zum Server. <strong>allow_url_fopen</strong> ist in der php.ini deaktiviert.',
                'type' => 'warning'
            ]);
        }

        if (version_compare(PHP_VERSION, self::REQUIRED_PHP_VERSION, '<')) {
            Notification::pushFlashMessage([
                'text' => 'Warnung: Die PHP Version ' . PHP_VERSION . ' wird nicht unterstützt. Der MMLC benötigt ' . self::REQUIRED_PHP_VERSION . ' oder höher.',
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
        $this->checkAccessRight();

        $selfUpdater = new SelfUpdater();
        $installedVersion = $selfUpdater->getInstalledVersion();
        $version = $selfUpdater->getNewestVersionInfo();

        $queryParams = $this->serverRequest->getQueryParams();
        $installVersion = $queryParams['install'] ?? '';

        if ($installVersion) {
            $selfUpdater->update($installVersion);
            return $this->redirect('/?action=selfUpdate');
        }

        // Postupdate ausführen, falls erforderlich
        $executed = $selfUpdater->checkAndDoPostUpdate();

        // Wenn der Postupdate durchgeführt werden musste, die Seite noch einmal
        // automatisch neu laden
        if ($executed) {
            return $this->redirect('/?action=selfUpdate');
        }

        $checkUpdate = $selfUpdater->checkUpdate();

        $comparator = new Comparator(new Parser());

        return $this->render('SelfUpdate', [
            'comparator' => $comparator,
            'version' => $version,
            'installedVersion' => $installedVersion,
            'serverName' => $_SERVER['SERVER_NAME'] ?? 'unknown Server Name'
        ]);
    }

    public function invokeIndex()
    {
        $this->checkAccessRight();

        $moduleLoader = ModuleLoader::getModuleLoader();
        $modules = $moduleLoader->loadAllVersionsWithLatestRemote();
        $modules = ModuleFilter::filterNewestOrInstalledVersion($modules);

        $heading = 'Alle Module';

        $queryParams = $this->serverRequest->getQueryParams();
        $filterModules = $queryParams['filterModules'] ?? '';

        if ($filterModules == 'loaded') {
            $modules = ModuleFilter::filterLoaded($modules);
            $heading = 'Geladene Module';
        } elseif ($filterModules == 'installed') {
            $modules = ModuleFilter::filterInstalled($modules);
            $heading = 'Installierte Module';
        } elseif ($filterModules == 'updatable') {
            $modules = ModuleFilter::filterUpdatable($modules);
            $heading = 'Aktualisierbare Module';
        } elseif ($filterModules == 'changed') {
            $modules = ModuleFilter::filterRepairable($modules);
            $heading = 'Geänderte Module';
        } elseif ($filterModules == 'notloaded') {
            $modules = ModuleFilter::filterNotLoaded($modules);
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
        $this->checkAccessRight();

        $queryParams = $this->serverRequest->getQueryParams();
        $archiveName = $queryParams['archiveName'] ?? '';
        $version = $queryParams['version'] ?? '';

        if ($version) {
            $moduleLoader = ModuleLoader::getModuleLoader();
            $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);
        } else {
            $moduleLoader = ModuleLoader::getModuleLoader();
            $modules = $moduleLoader->loadAllVersionsByArchiveNameWithLatestRemote($archiveName);
            $module = ModuleFilter::getLatestVersion($modules);
        }

        if (!$module) {
            $this->addModuleNotFoundNotification($archiveName, $version);
            return $this->redirect('/');
        }

        return $this->render('ModuleInfo', [
            'module' => $module
        ]);
    }

    public function invokeLazyModuleInfo()
    {
        $this->checkAccessRight();

        $queryParams = $this->serverRequest->getQueryParams();
        $archiveName = $queryParams['archiveName'] ?? '';
        $version = $queryParams['version'] ?? '';
        $data = $queryParams['data'] ?? '';

        $moduleLoader = ModuleLoader::getModuleLoader();
        $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if ($data == 'installationMd') {
            return ['content' => $module->getInstallationMd()];
        } elseif ($data == 'usageMd') {
            return ['content' => $module->getUsageMd()];
        } elseif ($data == 'changelogMd') {
            return ['content' => $module->getChangeLogMd()];
        } elseif ($data == 'readmeMd') {
            return ['content' => $module->getReadmeMd()];
        }
    }

    public function invokeLazyModuleUpdateCount()
    {
        $this->checkAccessRight();

        $value = $this->calcModuleUpdateCount();
        if ($value) {
            return ['content' => $value];
        }
    }

    public function invokeLazyModuleChangeCount()
    {
        $this->checkAccessRight();

        $value = $this->calcModuleChangeCount();
        if ($value) {
            return ['content' => $value];
        }
    }

    public function invokeLazySystemUpdateCount()
    {
        $this->checkAccessRight();

        $value = $this->calcSystemUpdateCount();
        if ($value) {
            return ['content' => $value];
        }
    }

    public function invokeInstall()
    {
        $this->checkAccessRight();

        $queryParams = $this->serverRequest->getQueryParams();
        $archiveName = $queryParams['archiveName'] ?? '';
        $version = $queryParams['version'] ?? '';

        $moduleLoader = new LocalModuleLoader();
        $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            $this->addModuleNotFoundNotification($archiveName, $version);
            return $this->redirect('/');
        }

        try {
            $moduleInstaller = new ModuleInstaller();
            //$moduleInstaller->install($module);
            //$moduleInstaller->installDependencies($module);
            $moduleInstaller->installWithDependencies($module);
        } catch (DependencyException $e) {
            Notification::pushFlashMessage([
                'text' => $e->getMessage(),
                'type' => 'error'
            ]);
        }

        return $this->redirectRef($archiveName, $module->getVersion());
    }

    private function invokeRevertChanges()
    {
        $this->checkAccessRight();

        $queryParams = $this->serverRequest->getQueryParams();
        $archiveName = $queryParams['archiveName'] ?? '';
        $version = $queryParams['version'] ?? '';

        $moduleLoader = new LocalModuleLoader();
        $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            $this->addModuleNotFoundNotification($archiveName, $version);
            return $this->redirect('/');
        }

        try {
            $moduleInstaller = new ModuleInstaller();
            $moduleInstaller->revertChanges($module);
        } catch (DependencyException $e) {
            Notification::pushFlashMessage([
                'text' => $e->getMessage(),
                'type' => 'error'
            ]);
        } catch (\RuntimeException $e) {
            Notification::pushFlashMessage([
                'text' => $e->getMessage(),
                'type' => 'error'
            ]);
        }

        return $this->redirectRef($archiveName, $module->getVersion());
    }

    public function invokeUninstall()
    {
        $this->checkAccessRight();

        $queryParams = $this->serverRequest->getQueryParams();
        $archiveName = $queryParams['archiveName'] ?? '';
        $version = $queryParams['version'] ?? '';

        $moduleLoader = new LocalModuleLoader();
        $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            $this->addModuleNotFoundNotification($archiveName, $version);
            return $this->redirect('/');
        }

        try {
            $moduleInstaller = new ModuleInstaller();
            $moduleInstaller->uninstall($module);
        } catch (DependencyException $e) {
            Notification::pushFlashMessage([
                'text' => $e->getMessage(),
                'type' => 'error'
            ]);
        }

        return $this->redirectRef($archiveName, $module->getVersion());
    }

    public function invokeUpdate()
    {
        $this->checkAccessRight();

        $queryParams = $this->serverRequest->getQueryParams();
        $archiveName = $queryParams['archiveName'] ?? '';
        $version = $queryParams['version'] ?? '';

        $moduleLoader = new LocalModuleLoader();
        $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            $this->addModuleNotFoundNotification($archiveName, $version);
            return $this->redirect('/');
        }

        $newModule = $module;

        try {
            $moduleInstaller = new ModuleInstaller();
            $newModule = $moduleInstaller->updateWithDependencies($module);
        } catch (DependencyException $e) {
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
        $this->checkAccessRight();

        $queryParams = $this->serverRequest->getQueryParams();
        $archiveName = $queryParams['archiveName'] ?? '';
        $version = $queryParams['version'] ?? '';

        $moduleLoader = RemoteModuleLoader::getModuleLoader();
        $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            $this->addModuleNotFoundNotification($archiveName, $version);
            return $this->redirect('/');
        }

        $moduleInstaller = new ModuleInstaller();
        if (!$moduleInstaller->pull($module)) {
            Notification::pushFlashMessage([
                'text' => "Fehler: Das Module <strong>$archiveName - $version</strong> konnte nicht geladen werden.",
                'type' => 'error'
            ]);
        }

        return $this->redirectRef($archiveName, $module->getVersion());
    }

    public function invokeLoadAndInstall()
    {
        $this->checkAccessRight();

        $queryParams = $this->serverRequest->getQueryParams();
        $archiveName = $queryParams['archiveName'] ?? '';
        $version = $queryParams['version'] ?? '';

        $moduleLoader = RemoteModuleLoader::getModuleLoader();
        $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            $this->addModuleNotFoundNotification($archiveName, $version);
            return $this->redirect('/');
        }

        $moduleInstaller = new ModuleInstaller();
        if (!$moduleInstaller->pull($module)) {
            Notification::pushFlashMessage([
                'text' => "Fehler: Das Module <strong>$archiveName - $version</strong> konnte nicht geladen werden.",
                'type' => 'error'
            ]);
            return $this->redirect('/');
        }

        $moduleLoader = new LocalModuleLoader();
        $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            $this->addModuleNotFoundNotification($archiveName, $version);
            return $this->redirect('/');
        }

        try {
            $moduleInstaller = new ModuleInstaller();
            $moduleInstaller->install($module);
            $moduleInstaller->installDependencies($module);
        } catch (DependencyException $e) {
            Notification::pushFlashMessage([
                'text' => $e->getMessage(),
                'type' => 'error'
            ]);
        }

        return $this->redirectRef($archiveName, $module->getVersion());
    }

    public function invokeUnloadLocalModule()
    {
        $this->checkAccessRight();

        $queryParams = $this->serverRequest->getQueryParams();
        $archiveName = $queryParams['archiveName'] ?? '';
        $version = $queryParams['version'] ?? '';

        $moduleLoader = new LocalModuleLoader();
        $module = $moduleLoader->loadByArchiveNameAndVersion($archiveName, $version);

        if (!$module) {
            $this->addModuleNotFoundNotification($archiveName, $version);
            return $this->redirect('/');
        }

        try {
            $moduleInstaller = new ModuleInstaller();
            $moduleInstaller->delete($module);
        } catch (DependencyException $e) {
            Notification::pushFlashMessage([
                'text' => $e->getMessage(),
                'type' => 'error'
            ]);
        }

        return $this->redirect('/');
    }

    public function invokeReportIssue()
    {
        $this->checkAccessRight();

        $parsedBody = $this->serverRequest->getParsedBody();
        if (isset($parsedBody['send_mail'])) {
            SendMail::sendIssue();
        }

        return $this->render('ReportIssue');
    }

    public function invokeSupport()
    {
        $this->checkAccessRight();

        return $this->render('Support');
    }

    public function invokeSettings()
    {
        $this->checkAccessRight();

        /**
         * Save submitted form input to config.
         */
        if ($this->isPostRequest()) {
            $parsedBody = $this->serverRequest->getParsedBody();

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

            if (isset($parsedBody['modulesLocalDir'])) {
                Config::setModulesLocalDir($parsedBody['modulesLocalDir']);
            }

            if (isset($parsedBody['installMode'])) {
                Config::setInstallMode($parsedBody['installMode']);
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
        $moduleLoader = LocalModuleLoader::getModuleLoader();
        $modules = $moduleLoader->loadAllVersions();
        $modules = ModuleFilter::filterInstalled($modules);
        return count(ModuleFilter::filterUpdatable($modules));
    }

    public function calcModuleChangeCount()
    {
        $moduleLoader = LocalModuleLoader::getModuleLoader();
        $modules = $moduleLoader->loadAllVersions();
        return count(ModuleFilter::filterRepairable($modules));
    }

    public function calcSystemUpdateCount()
    {
        $selfUpdater = new SelfUpdater();
        $checkUpdate = $selfUpdater->checkUpdate();
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
}
