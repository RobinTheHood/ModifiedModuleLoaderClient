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

class Config
{
    /**
     * @var array $configurationFile will have the contents of the config.php
     * which is a multidimenional array
     */
    protected static $configurationFile;

    /**
     * Read the config file
     */
    protected static function getConfigContents()
    {
        if (file_exists(App::getConfigRoot() . '/config.php'))
        {
            include App::getConfigRoot() . '/config.php';

            self::$configurationFile = $configuration;
        }
        else
        {
            self::$configurationFile = [];
        }
    }

    /**
     * Write the config file
     */
    protected static function setConfigContents()
    {
        self::getConfigContents();

        $fileContents = var_export(self::$configurationFile, true);
        $fileContents = str_replace('array', '<?php' . PHP_EOL . '$configuration =', $fileContents);
        $fileContents = str_replace('(', '[', $fileContents);
        $fileContents = str_replace(')', ']', $fileContents);
        $fileContents .= ';';

        file_put_contents(App::getConfigRoot() . '/configNew.php', $fileContents);

        /**
         * Determine if new file was written properly and delete the old config
         */
        var_dump( hash( 'crc32', file_get_contents( App::getConfigRoot() . '/configNew.php' ) ) );
        var_dump( hash( 'crc32', implode( PHP_EOL, self::$configurationFile ) ) );
    }

    public static function getOptions()
    {

    }

    /**
     * Allows manipulating values in the config.php
     *
     * As opposed to other static methods in this class,
     * this method will read and write the config
     * after it has manipulated the config.
     */
    public static function setOptions(array $options)
    {
        $configPath = App::getConfigRoot() . '/config.php';
        $configOld = file_get_contents($configPath);
        $configNew = $configOld;

        foreach ($options as $key => $value) {
            $matches = [];
            $regex = '/\'(' . $key . ')\'[ ]*=>[ ]*\'(.*)\'/';

            preg_match($regex, $configOld, $matches);

            switch (count($matches)) {
                case 3:
                    $configNew = str_replace($matches[0], str_replace($matches[2], $value, $matches[0]), $configOld);
                    break;

                case 0:
                    echo 'Option "' . $key . '" does not not exist in "' . $configPath . '".';
                    break;
            }

            file_put_contents($configPath, $configNew);
        }
    }

    /*
    public static function setOptions(array $options)
    {
        self::getConfigContents();

        foreach ($options as $key => $value) {
            self::$configurationFile[$key] = $value;
        }

        self::setConfigContents();
    }
    */

    /**
     * username
     */
    public static function getUsername()
    {
        self::getConfigContents();

        return isset(self::$configurationFile['username']) ? self::$configurationFile['username'] : '';
    }

    public static function setUsername(string $newUsername)
    {
        self::getConfigContents();

        self::$configurationFile['username'] = $newUsername;

        self::setConfigContents();
    }

    /**
     * password
     *
     * Sets a new password used for logging in.
     * Password muss be parsed as a hash.
     *
     */
    public static function getPassword()
    {
        self::getConfigContents();

        return isset(self::$configurationFile['password']) ? self::$configurationFile['password'] : '';
    }

    public static function setPassword(string $newPassword)
    {
        self::getConfigContents();

        self::$configurationFile['password'] = $newPassword;

        self::setConfigContents();
    }

    /**
     * adminDir
     */
    public static function getAdminDir()
    {
        self::getConfigContents();

        return isset(self::$configurationFile['adminDir']) ? self::$configurationFile['adminDir'] : '';
    }

    public static function setAdminDir(string $newAdminDir)
    {
        self::getConfigContents();

        self::$configurationFile['adminDir'] = $newAdminDir;

        self::setConfigContents();
    }

    /**
     * modulesLocalDir
     */
    public static function getModulesLocalDir()
    {
        self::getConfigContents();

        return isset(self::$configurationFile['modulesLocalDir']) ? self::$configurationFile['modulesLocalDir'] : '';
    }

    public static function setModulesLocalDir(string $newModulesLocalDir)
    {
        self::getConfigContents();

        self::$configurationFile['modulesLocalDir'] = $newModulesLocalDir;

        self::setConfigContents();
    }

    /**
     * remoteAddress
     */
    public static function getRemoteAddress()
    {
        self::getConfigContents();

        return isset(self::$configurationFile['remoteAddress']) ? self::$configurationFile['remoteAddress'] : '';
    }

    public static function setRemoteAddress(string $newRemoteAddress)
    {
        self::getConfigContents();

        self::$configurationFile['remoteAddress'] = $newRemoteAddress;

        self::setConfigContents();
    }

    /**
     * installMode
     */
    public static function getInstallMode()
    {
        self::getConfigContents();

        return isset(self::$configurationFile['installMode']) ? self::$configurationFile['installMode'] : '';
    }

    public static function setInstallMode(string $newInstallMode)
    {
        self::getConfigContents();

        self::$configurationFile['installMode'] = $newInstallMode;

        self::setConfigContents();
    }

    /**
     * selfUpdate
     */
    public static function getSelfUpdate()
    {
        self::getConfigContents();

        return isset(self::$configurationFile['selfUpdate']) ? self::$configurationFile['selfUpdate'] : '';
    }

    public static function setSelfUpdate(string $newSelfUpdate)
    {
        self::getConfigContents();

        self::$configurationFile['selfUpdate'] = $newSelfUpdate;

        self::setConfigContents();
    }

    /**
     * accessToken
     */
    public static function getAccessToken()
    {
        self::getConfigContents();

        return isset(self::$configurationFile['accessToken']) ? self::$configurationFile['accessToken'] : '';
    }

    public static function setAccessToken(string $newAccessToken)
    {
        self::getConfigContents();

        self::$configurationFile['accessToken'] = $newAccessToken;

        self::setConfigContents();
    }

    /**
     * exceptionMonitorIp
     */
    public static function getExceptionMonitorIp()
    {
        self::getConfigContents();

        return isset(self::$configurationFile['exceptionMonitorIp']) ? self::$configurationFile['exceptionMonitorIp'] : '';
    }

    public static function setExceptionMonitorIp(string $newExceptionMonitorIp)
    {
        self::getConfigContents();

        self::$configurationFile['exceptionMonitorIp'] = $newExceptionMonitorIp;

        self::setConfigContents();
    }

    /**
     * exceptionMonitorDomain
     */
    public static function getExceptionMonitorDomain()
    {
        self::getConfigContents();

        return isset(self::$configurationFile['exceptionMonitorDomain']) ? self::$configurationFile['exceptionMonitorDomain'] : '';
    }

    public static function setExceptionMonitorDomain(string $newExceptionMonitorDomain)
    {
        self::getConfigContents();

        self::$configurationFile['exceptionMonitorDomain'] = $newExceptionMonitorDomain;

        self::setConfigContents();
    }

    /**
     * exceptionMonitorMail
     */
    public static function getExceptionMonitorMail(): ?string
    {
        self::getConfigContents();

        $mail = isset(self::$configurationFile['exceptionMonitorMail']) ? self::$configurationFile['exceptionMonitorMail'] : '';

        if (trim($mail) !== '') {
            return $mail;
        }
        else {
            return null;
        }
    }

    public static function setExceptionMonitorMail(string $newExceptionMonitorMail)
    {
        self::getConfigContents();

        self::$configurationFile['exceptionMonitorMail'] = $newExceptionMonitorMail;

        self::setConfigContents();
    }
}
