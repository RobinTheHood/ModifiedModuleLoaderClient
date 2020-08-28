<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('VERSION', '0.5.0');

class Installer
{
    const REMOTE_ADDRESS = 'https://app.module-loader.de/Downloads/ModifiedModuleLoaderClient.tar';
    const REQUIRED_PHP_VERSION = '7.1.12';

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
        } else if (!$this->isInstalled()) {
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
            $errors[] = 'Current PHP version is ' . PHP_VERSION . '. The MMLC needs version <strong>' . self::REQUIRED_PHP_VERSION . '</strong> or higher.';
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
        $remoteAddress = self::REMOTE_ADDRESS;
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
                        <code>' . __DIR__ . '/ModifiedModuleLoderClient</code>
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

    public static function showInstalled()
    {
        $shopUrl = $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
        $mmlcUrl = $shopUrl . '/ModifiedModuleLoaderClient';
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
        $shopUrl = $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
        $mmlcUrl = $shopUrl . '/ModifiedModuleLoaderClient';

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
