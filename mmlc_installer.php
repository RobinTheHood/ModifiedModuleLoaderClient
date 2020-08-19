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

define('VERSION', '0.4.0');

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
        $systemCheck = true;

        $errors = [];
        if (!ini_get('allow_url_fopen')) {
            $systemCheck = false;
            $errors[] = 'No connection to server. <strong>allow_url_fopen</strong> has to be activated in php.ini.';
        }

        if (version_compare(PHP_VERSION, self::REQUIRED_PHP_VERSION, '<')) {
            $systemCheck = false;
            $errors[] = 'Current PHP version is ' . PHP_VERSION . '. The MMLC needs version <strong>' . self::REQUIRED_PHP_VERSION . '</strong> or higher.';
        }

        if (!$systemCheck) {
            echo Template::showSystemCheck($errors);
        } else if (!$this->isInstalled()) {
            echo Template::showInstall();
        } else {
            echo Template::showInstalled();
        }
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

        $string = file_get_contents(__DIR__ . '/ModifiedModuleLoaderClient/config/config.php');
        $string = str_replace("username' => 'root',", "username' => '$user',", $string);
        $string = str_replace("'password' => 'root',", "'password' => '$passwordHash',", $string);
        file_put_contents(__DIR__ . '/ModifiedModuleLoaderClient/config/config.php', $string);
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
                <h1>ModifiedModuleLoaderClient Installer v' . VERSION . '</h1>
                <div>Please setup a <strong>username</strong> and <strong>password</strong>.</div>
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
                <h1>ModifiedModuleLoaderClient Installer v' . VERSION . '</h1>
                <div>ModifiedModuleLoaderClient system check failed.</div>
                <br>
                <div style="color: red">' . $errorStr . '</div>
            </div>
        ';
    }

    public static function showInstalled()
    {
        $shopURL = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
        $mmlcURL = $shopURL . '/ModifiedModuleLoaderClient';
        $installerFilepath = $_SERVER['SCRIPT_FILENAME'];


        /**
         * Installer automatisch löschen
         */
        unlink($installerFilepath);


        /**
         * Falls installer nicht gelöscht wurde, Nachricht anzeigen
         */
        if ( file_exists($installerFilepath) ) {
            return
                self::style() . '
                <div style="text-align: center">
                    <h1> ModifiedModuleLoaderClient Installer v' . VERSION . '</h1>
                    <div>ModifiedModuleLoaderClient is already installed.</div>
                    <div>You can now delete the mmlc_installer.php</div>
                    <br><br>
                    <div>
                        Open: <br>
                        <a href="' . $mmlcURL . '">
                            ' . $shopURL . '
                        </a>
                    </div>
                </div>
            ';
        }

        /**
         * Weiterleiten, wenn Datei gelöscht werden konnte
         */
        header('Location: ModifiedModuleLoaderClient');
    }

    public static function showInstallDone()
    {
        $shopURL = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
        $mmlcURL = $shopURL . '/ModifiedModuleLoaderClient';

        return
            self::style() . '
            <div style="text-align: center">
                <h1>ModifiedModuleLoaderClient Installer v' . VERSION . '</h1>
                <div>ModifiedModuleLoaderClient was installed.</div>
                <div>You can now delete the mmlc_installer.php</div>
                <br><br>
                <div>
                    Open: <br>
                    <a href="' . $mmlcURL . '">
                        ' . $shopURL . '
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
                    background-color: #32cfff;
                }

                code {
                    max-width: 800px;
                    display: block;
                    padding: 5px;
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
