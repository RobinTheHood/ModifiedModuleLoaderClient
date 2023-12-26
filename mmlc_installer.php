<?php // @phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 * @phpcs:disable Generic.Files.LineLength.TooLong
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
 */
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

define('VERSION', '0.8.1');

class Installer
{
    private const REMOTE_ADDRESS = 'https://app.module-loader.de';
    private const INSTALL_FILE = '/Downloads/ModifiedModuleLoaderClient.tar';
    private const REQUIRED_PHP_VERSION = '7.4.0';
    private const REQUIRED_MODIFIED_VERSIONS = [
        "2.0.3.0",
        "2.0.4.1",
        "2.0.4.2",
        "2.0.5.0",
        "2.0.5.1",
        "2.0.6.0",
        "2.0.7.0",
        "2.0.7.1",
        "2.0.7.2",
        "3.0.0"
    ];

    public function invoke()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : '';

        if ($action == 'install') {
            $this->invokeInstall();
        } else {
            $this->invokeIndex();
        }
    }

    public function invokeIndex()
    {
        $errors = $this->doSystemCheck();

        if ($errors) {
            echo Template::showSystemCheck($errors);
        } elseif (!$this->isInstalled()) {
            echo Template::showInstall();
        } else {
            echo Template::showInstalled();
        }
    }

    public function doSystemCheck()
    {
        $errors = [];

        if (!ini_get('allow_url_fopen')) {
            $errors[] = 'No connection to server. <strong>allow_url_fopen</strong> has to be activated in php.ini.';
        }

        if (version_compare(PHP_VERSION, self::REQUIRED_PHP_VERSION, '<')) {
            $errors[] = 'Your current PHP version is ' . PHP_VERSION . '. The MMLC needs version <strong>' . self::REQUIRED_PHP_VERSION . '</strong> or higher.';
        }

        if (!file_exists(__DIR__ . '/includes/classes/modified_api.php')) {
            $errors[] = '<code style="display: inline; padding: 2px 4px;">' . __DIR__ . '</code> is the wrong installation directory. Please use the shop root.';
        }

        $adminDirs = $this->getAdminDirs();
        if (count($adminDirs) < 1) {
            $errors[] = 'Can not find a admin dir.';
        }

        if (count($adminDirs) > 1) {
            $errors[] = 'Several admin directories found.';
        }

        $currentModifiedVersion = $this->getModifiedVersion();
        if (!in_array($currentModifiedVersion, self::REQUIRED_MODIFIED_VERSIONS)) {
            $errors[] = 'Your current modified version is ' . $currentModifiedVersion . '. The MMLC supports modified versions <strong>' . implode(', ', self::REQUIRED_MODIFIED_VERSIONS) . '</strong>.';
        }

        return $errors;
    }

    public function invokeInstall()
    {
        $user = trim(isset($_POST['user']) ? $_POST['user'] : '');
        $password = trim(isset($_POST['password']) ? $_POST['password'] : '');
        $passwordRe = trim(isset($_POST['passwordRe']) ? $_POST['passwordRe'] : '');

        $error = '';
        if (strlen($user) < 3) {
            $error = 'User length musst be 3 or longer.';
        } elseif (strlen($password) < 8) {
            $error = 'Password length musst be 8 or longer.';
        } elseif ($password != $passwordRe) {
            $error = 'Passwords are not equal.';
        }

        if (!$error) {
            $this->download();
            $this->untar();
            $this->setLogin($user, $password);
            $this->setUpAccessToken();
            $this->cleanUp();

            echo Template::showInstallDone();
        } else {
            echo Template::showInstall($error);
        }
    }

    public function isInstalled()
    {
        if (file_exists(__DIR__ . '/ModifiedModuleLoaderClient')) {
            return true;
        }
        return false;
    }

    public function download()
    {
        $remoteAddress = self::REMOTE_ADDRESS . self::INSTALL_FILE;
        $remoteAddress .= '?sn=' . $_SERVER['SERVER_NAME'] ?? 'no-server-name';
        $tarBall = file_get_contents($remoteAddress);

        if (!$tarBall) {
            return false;
        }

        file_put_contents('ModifiedModuleLoaderClient.tar', $tarBall);
    }

    public function untar()
    {
        $tarBall = new \PharData('ModifiedModuleLoaderClient.tar');
        $tarBall->extractTo(__DIR__, null, true);
    }

    public function setLogin($user, $password)
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $this->setConfig([
            'username' => $user,
            'password' => $passwordHash
        ]);
    }

    public function setUpAccessToken()
    {
        $accessToken = md5(uniqid());

        $this->setConfig([
            'accessToken' => $accessToken
        ]);
    }

    /**
     * Read config and set new values with regex (and write to file)
     */
    protected function setConfig(array $options)
    {
        foreach ($options as $key => $value) {
            $oldConfig = file_get_contents(__DIR__ . '/ModifiedModuleLoaderClient/config/config.php');
            $matches = [];

            /**
             * Look for line in config which matches:
             * '$key' => 'foobar' (i. e.: 'username' => 'root')
             *
             * Look for $value in found line and replace it:
             * '$key' => 'foobar' becomes '$key' => '$value'
             */
            $regex = '/\'(' . $key . ')\'[ ]*=>[ ]*\'(.*)\'/';

            preg_match($regex, $oldConfig, $matches);

            $newConfig = str_replace($matches[0], str_replace($matches[2], $value, $matches[0]), $oldConfig);

            file_put_contents(__DIR__ . '/ModifiedModuleLoaderClient/config/config.php', $newConfig);
        }
    }

    public function cleanUp()
    {
        @unlink('ModifiedModuleLoaderClient.tar');
    }

    private function getModifiedVersion(): string
    {
        $adminDirs = $this->getAdminDirs();
        $adminDir = $adminDirs[0] ?? 'admin';

        $path = __DIR__ . '/' . $adminDir . '/includes/version.php';

        if (!file_exists($path)) {
            return 'unknown';
        }

        $fileStr = file_get_contents($path);

        // Try DB_VERSION
        $pattern = "/MOD_(\d+\.\d+\.\d+(\.\d+)?)\'\);/";
        if (preg_match($pattern, $fileStr, $matches)) {
            return $matches[1];
        }

        // Try MAJOR_VERSION ans MINOR_VERSION
        preg_match('/MAJOR_VERSION.+?\'([\d\.]+)\'/', $fileStr, $versionMajor);
        preg_match('/MINOR_VERSION.+?\'([\d\.]+)\'/', $fileStr, $versionMinor);
        $versionMajor[1] = $versionMajor[1] ?? '';
        $versionMinor[1] = $versionMinor[1] ?? '';
        if ($versionMajor[1] && $versionMinor[1]) {
            return $versionMajor[1] . '.' . $versionMinor[1];
        }

        return 'unknown';
    }

    private function getAdminDirs()
    {
        $adminDirs = [];

        $filePaths = scandir(__DIR__);
        foreach ($filePaths as $filePath) {
            $fileName = basename($filePath);
            $fileNameLower = strtolower($fileName);

            if (strpos($fileNameLower, 'admin') !== 0) {
                continue;
            }

            if (!file_exists($filePath . '/check_update.php')) {
                continue;
            }

            $adminDirs[] = $fileName;
        }

        return $adminDirs;
    }
}

class Template
{
    public static function showInstall($error = '')
    {
        $errorHtml = '';
        if ($error) {
            $errorHtml = '<div class="error">' . $error . '</div><br>';
        }

        return
            self::style() . '
            <div style="text-align: center">
                <h1>Modified Module Loader Client Installer v' . VERSION . '</h1>
                <div>
                    With this login data you can get access to the MMLC after installation.<br>
                    For more information visit <a target="_blank" href="https://module-loader.de">module-loader.de</a>
                </div>

                <br>

                <div>
                    Please setup a <strong>username</strong> and <strong>password</strong>.<br>
                </div>

                <br>

                <form action="?action=install" method="post">
                    ' . $errorHtml . '
                    <div>
                        <span class="input-text">User:</span>
                        <span class="input"><input type="text" name="user" value=""></span>
                    </div>

                    <div>
                        <span class="input-text">Password:</span>
                        <span class="input"><input type="password" name="password" value=""></span>
                    </div>

                    <div>
                        <span class="input-text">Password-Repeat:</span>
                        <span class="input"><input type="password" name="passwordRe" value=""></span>
                    </div>

                    <br>
                    <div>
                        Install-Directory:
                        <br><br>
                        <code>' . __DIR__ . '/ModifiedModuleLoaderClient</code>
                    </div>

                    <br><br>

                    <div>
                        <input type="submit" value="Install now">
                    </div>
                </form>
            </div>
        ';
    }

    public static function showSystemCheck($errors)
    {
        $errorStr = '';
        foreach ($errors as $error) {
            $errorStr .= "<div>$error</div><br>";
        }

        return
            self::style() . '
            <div style="text-align: center">
                <h1>Modified Module Loader Client Installer v' . VERSION . '</h1>
                <div>Modified Module Loader Client system check failed.</div>
                <br>
                <div style="color: red">' . $errorStr . '</div>
            </div>
        ';
    }

    public static function getShopUrl()
    {
        $dirname = dirname($_SERVER['PHP_SELF']);
        $dirname = $dirname == '/' ? '' : $dirname;
        return $_SERVER['HTTP_HOST'] . $dirname;
    }

    public static function showInstalled()
    {
        $mmlcUrl = self::getShopUrl() . '/ModifiedModuleLoaderClient';
        $installerFilepath = __FILE__;

        // Installer automatisch löschen
        //unlink($installerFilepath);

        if (!file_exists($installerFilepath)) {
            // Weiterleiten, wenn Datei gelöscht werden konnte
            header('Location: ' . $mmlcUrl);
            die();
        }

        // Falls installer nicht gelöscht wurde, Nachricht anzeigen
        return
            self::style() . '
            <div style="text-align: center">
                <h1>Modified Module Loader Client Installer v' . VERSION . '</h1>
                <div>Modified Module Loader Client is already installed.</div>
                <div>You can now delete the mmlc_installer.php</div>
                <br><br>
                <div>
                    Open the MMLC: <br>
                    <a href="//' . $mmlcUrl . '">
                        ' . $mmlcUrl . '
                    </a>
                </div>
            </div>
        ';
    }

    public static function showInstallDone()
    {
        $mmlcUrl = self::getShopUrl() . '/ModifiedModuleLoaderClient';

        return
            self::style() . '
            <div style="text-align: center">
                <h1> Modified Module Loader Client Installer v' . VERSION . '</h1>
                <div>Modified Module Loader Client is ready installed.</div>
                <div>You can now delete the mmlc_installer.php</div>
                <br><br>
                <div>
                    Open the MMLC: <br>
                    <a href="//' . $mmlcUrl . '">
                        ' . $mmlcUrl . '
                    </a>
                </div>
            </div>
        ';
    }

    public static function style()
    {
        return '
            <style>
                body {
                    margin: 0px;
                    font-family: Helvetica Neue, Helvetica, Arial, sans-serif;
                }

                .input-text {
                    display: inline-block;
                    width: 200px;
                    text-align: right;
                    margin-right: 5px;
                }

                .input {
                    display: inline-block;
                    width: 200px;
                    text-align: left;
                    margin-left: 5px;
                }

                h1 {
                    padding: 10px;
                    color: #ffffff;
                    background-color: #007bff;
                }

                code {
                    max-width: 800px;
                    display: block;
                    padding: 10px;
                    border: 1px solid #cccccc;
                    background-color: #eeeeee;
                    margin-left: auto;
                    margin-right: auto;
                }

                .error {
                    max-width: 800px;
                    border: 1px solid #ff0000;
                    padding: 10px;
                    margin-left: auto;
                    margin-right: auto;
                }
            </style>
        ';
    }
}

$installer = new Installer();
$installer->invoke();
