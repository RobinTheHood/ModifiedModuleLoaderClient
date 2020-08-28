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
     * @var array $configuration will have the contents of the config.php.
     */
    protected static $configuration = [];

    /**
     * @param bool $cache whether to load from file (true) or not.
     *
     * @return array config.php contents.
     */
    protected static function getConfiguration(bool $cache = true): array
    {
        $configurationPath = App::getConfigRoot() . '/config.php';

        /**
         * Only load config from file when it exists and either:
         * - Cache is disabled
         * - Config is empty
         */
        if ((!$cache || count(self::$configuration) === 0) && file_exists($configurationPath))
        {
            include $configurationPath;

            /**
             * $configurationPath contains an array named $configuration.
             */
            self::$configuration = $configuration;
        }

        return self::$configuration;
    }

    /**
     * Manipulate values in the config.php
     *
     * @param array $options
     */
    public static function setConfiguration(array $options)
    {
        $configPath = App::getConfigRoot() . '/config.php';
        $configOld = file_get_contents($configPath);
        $configNew = '';

        foreach ($options as $key => $value)
        {
            $matches = [];

            /**
             * Look for line in config which matches:
             * '$key' => 'foobar' (i. e.: 'username' => 'root')
             *
             * Look for $value in found line and replace it:
             * '$key' => 'foobar' becomes '$key' => '$value'
             */
            $regex = '/\'(' . $key . ')\'[ ]*=>[ ]*\'(.*)\'/';

            preg_match($regex, $configOld, $matches);

            switch (count($matches))
            {
                case 3:
                    $configNew = str_replace($matches[0], str_replace($matches[2], $value, $matches[0]), $configOld);
                    $configOld = $configNew;
                    break;

                case 0:
                    /**
                     * To do: add option if it doesn't exist
                     * instead of showing an error.
                     */
                    $configNew = $configOld;
                    echo 'Option "' . $key . '" does not not exist in "' . $configPath . '".';
                    break;
            }
        }

        file_put_contents($configPath, $configNew);
    }

    /**
     * Get any option from config.
     *
     * @param array $options An array of options you would like to retrieve.
     *
     * @return mixed Returns the requested options as an array
     * if they were found. Return the requested option as a String
     * if there is just one. Return null if the option was not found.
     */
    public static function getOptions(array $options = [])
    {
        $configuration = self::getConfiguration();
        $configurationValues = [];

        /**
         * Return all options if none are specified.
         */
        if (!$options)
        {
            return $configuration;
        }

        /**
         * Iterate through all specified options and list them
         * in $configurationValues.
         */
        foreach ($options as $key)
        {
            if (isset($configuration[$key]) && $configuration[$key] !== '')
            {
                $configurationValues[$key] = $configuration[$key];
            }
        }

        /**
         * Output different data types based on found value(s).
         */
        switch (count($configurationValues)) {
            case 0:
                return null;
                break;

            case 1:
                $onlyOneKey = key($configurationValues);

                return $configurationValues[$onlyOneKey];
                break;

            default:
                return $configurationValues;
                break;
        }
    }

    /**
     * Get username from config.
     *
     * @return string|null Returns the username from config or null.
     */
    public static function getUsername(): ?string
    {
        return self::getOptions(['username']);
    }

    /**
     * Set username in config.
     *
     * @param string $newUsername.
     */
    public static function setUsername(string $newUsername)
    {
        self::setConfiguration([$newUsername]);
    }

    /**
     * Get password from config.
     *
     * @return string|null Returns the password which is expected to be a hash or null.
     */
    public static function getPassword(): ?string
    {
        return self::getOptions(['password']);
    }

    /**
     * Set password in config.
     *
     * @param string $newPassword Sets a new password used for logging in.
     * Password should be a hash.
     */
    public static function setPassword(string $newPassword)
    {
        self::setConfiguration([$newPassword]);
    }

    /**
     * Get adminDir from config.
     *
     * @return string|null Returns the adminDir from config or null.
     */
    public static function getAdminDir(): ?string
    {
        return self::getOptions(['adminDir']);
    }

    /**
     * Set adminDir in config.
     *
     * @param string $newAdminDir.
     */
    public static function setAdminDir(string $newAdminDir)
    {
        self::setConfiguration([$newAdminDir]);
    }

    /**
     * Get modulesLocalDir from config.
     *
     * @return string|null Returns the modulesLocalDir from config or null.
     */
    public static function getModulesLocalDir(): ?string
    {
        return self::getOptions(['modulesLocalDir']);
    }

    /**
     * Set modulesLocalDir in config.
     *
     * @param string $newModulesLocalDir.
     */
    public static function setModulesLocalDir(string $newModulesLocalDir)
    {
        self::setConfiguration([$newModulesLocalDir]);
    }

    /**
     * Get remoteAddress from config.
     *
     * @return string|null Returns the remoteAddress from config or null.
     */
    public static function getRemoteAddress(): ?string
    {
        return self::getOptions(['remoteAddress']);
    }

    /**
     * Set remoteAddress in config.
     *
     * @param string $newRemoteAddress.
     */
    public static function setRemoteAddress(string $newRemoteAddress)
    {
        self::setConfiguration([$newRemoteAddress]);
    }

    /**
     * Get installMode from config.
     *
     * @return string|null Returns the installMode from config or null.
     */
    public static function getInstallMode(): ?string
    {
        return self::getOptions(['installMode']);
    }

    /**
     * Set installMode in config.
     *
     * @param string $newInstallMode.
     */
    public static function setInstallMode(string $newInstallMode)
    {
        self::setConfiguration([$newInstallMode]);
    }

    /**
     * Get selfUpdate from config.
     *
     * @return string|null Returns the selfUpdate from config or null.
     */
    public static function getSelfUpdate(): ?string
    {
        return self::getOptions(['selfUpdate']);
    }

    /**
     * Set selfUpdate in config.
     *
     * @param string $newSelfUpdate.
     */
    public static function setSelfUpdate(string $newSelfUpdate)
    {
        self::setConfiguration([$newSelfUpdate]);
    }

    /**
     * Get accessToken from config.
     *
     * @return string|null Returns the accessToken from config or null.
     */
    public static function getAccessToken(): ?string
    {
        return self::getOptions(['accessToken']);
    }

    /**
     * Set accessToken in config.
     *
     * @param string $newAccessToken.
     */
    public static function setAccessToken(string $newAccessToken)
    {
        self::setConfiguration([$newAccessToken]);
    }

    /**
     * Get exceptionMonitorIp from config.
     *
     * @return string|null Returns the exceptionMonitorIp from config or null.
     */
    public static function getExceptionMonitorIp(): ?string
    {
        return self::getOptions(['exceptionMonitorIp']);
    }

    /**
     * Set exceptionMonitorIp in config.
     *
     * @param string $newExceptionMonitorIp.
     */
    public static function setExceptionMonitorIp(string $newExceptionMonitorIp)
    {
        self::setConfiguration([$newExceptionMonitorIp]);
    }

    /**
     * Get exceptionMonitorDomain from config.
     *
     * @return string|null Returns the exceptionMonitorDomain from config or null.
     */
    public static function getExceptionMonitorDomain(): ?string
    {
        return self::getOptions(['exceptionMonitorDomain']);
    }

    /**
     * Set exceptionMonitorDomain in config.
     *
     * @param string $newExceptionMonitorDomain.
     */
    public static function setExceptionMonitorDomain(string $newExceptionMonitorDomain)
    {
        self::setConfiguration([$newExceptionMonitorDomain]);
    }

    /**
     * Get exceptionMonitorMail from config.
     *
     * @return string|null Returns the exceptionMonitorMail from config or null.
     */
    public static function getExceptionMonitorMail(): ?string
    {
        /**
         * Expect a string or null
         * depending if the user specified an email address.
         * You will not receive an empty string.
         */
        $exceptionMonitorMail = self::getOptions(['exceptionMonitorMail']);

        return $exceptionMonitorMail;
    }

    /**
     * Set exceptionMonitorMail in config.
     *
     * @param string $newExceptionMonitorMail.
     */
    public static function setExceptionMonitorMail(string $newExceptionMonitorMail)
    {
        self::setConfiguration([$newExceptionMonitorMail]);
    }
}
