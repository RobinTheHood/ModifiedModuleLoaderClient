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

        if (!file_exists($configurationPath)) {
            throw new \RuntimeException('Configuration not found. The file "' . $configurationPath . '" does not seem to exist.');
        }

        /**
         * Only load config from file when either:
         * - Cache is disabled
         * - Config is empty
         */
        if (!$cache || !self::$configuration) {
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
    public static function setConfiguration(array $options): void
    {
        $configPath = App::getConfigRoot() . '/config.php';
        $configOld = file_get_contents($configPath);
        $configNew = '';

        foreach ($options as $key => $value) {
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

            switch (count($matches)) {
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
                    throw new \RuntimeException('Cannot write option. Option "' . $key . '" does not not exist in ' . $configPath . '.');
                    break;
            }
        }

        file_put_contents($configPath, $configNew);
    }

    /**
     * Get an option from config.
     *
     * @param string $option The option to retrieve.
     *
     * @return string|null Returns the requested option
     * or null if it was not found.
     */
    public static function getOption(string $option = ''): ?string
    {
        $configuration = self::getConfiguration();

        if (isset($configuration[$option]) && $configuration[$option] !== '')
        {
            return $configuration[$option];
        }

        return null;
    }

    /**
     * Get any options from config.
     *
     * If you parse multiple options, only the ones found will be returned.
     * The invalid options will be silently ignored. Null is only returned if
     * none of the specified options were found.
     *
     * @param array $options An array of options you would like to retrieve.
     *
     * @return array|null Returns the requested options if they were found.
     * Returns null if no options were found.
     */
    public static function getOptions(array $options = []): ?array
    {
        $configuration = self::getConfiguration();
        $configurationValues = [];

        /**
         * Return all options if none are specified.
         */
        if (!$options) {
            return $configuration;
        }

        /**
         * Iterate through all specified options and list them
         * in $configurationValues.
         */
        foreach ($options as $key) {
            if (isset($configuration[$key]) && $configuration[$key] !== '')
            {
                $configurationValues[$key] = $configuration[$key];
            }
        }

        return $configurationValues ? $configurationValues : null;
    }

    /**
     * Get username from config.
     *
     * @return string|null Returns the username from config or null.
     */
    public static function getUsername(): ?string
    {
        return self::getOption('username');
    }

    /**
     * Set username in config.
     *
     * @param string $newUsername.
     */
    public static function setUsername(string $newUsername): void
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
        return self::getOption('password');
    }

    /**
     * Set password in config.
     *
     * @param string $newPassword Sets a new password used for logging in.
     * The password will be hashed.
     */
    public static function setPassword(string $newPassword): void
    {
        self::setConfiguration([password_hash($newPassword)]);
    }

    /**
     * Get adminDir from config.
     *
     * @return string|null Returns the adminDir from config or null.
     */
    public static function getAdminDir(): ?string
    {
        return self::getOption('adminDir');
    }

    /**
     * Set adminDir in config.
     *
     * @param string $newAdminDir.
     */
    public static function setAdminDir(string $newAdminDir): void
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
        return self::getOption('modulesLocalDir');
    }

    /**
     * Set modulesLocalDir in config.
     *
     * @param string $newModulesLocalDir.
     */
    public static function setModulesLocalDir(string $newModulesLocalDir): void
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
        return self::getOption('remoteAddress');
    }

    /**
     * Set remoteAddress in config.
     *
     * @param string $newRemoteAddress.
     */
    public static function setRemoteAddress(string $newRemoteAddress): void
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
        return self::getOption('installMode');
    }

    /**
     * Set installMode in config.
     *
     * @param string $newInstallMode.
     */
    public static function setInstallMode(string $newInstallMode): void
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
        return self::getOption('selfUpdate');
    }

    /**
     * Set selfUpdate in config.
     *
     * @param string $newSelfUpdate.
     */
    public static function setSelfUpdate(string $newSelfUpdate): void
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
        return self::getOption('accessToken');
    }

    /**
     * Set accessToken in config.
     *
     * @param string $newAccessToken.
     */
    public static function setAccessToken(string $newAccessToken): void
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
        return self::getOption('exceptionMonitorIp');
    }

    /**
     * Set exceptionMonitorIp in config.
     *
     * @param string $newExceptionMonitorIp.
     */
    public static function setExceptionMonitorIp(string $newExceptionMonitorIp): void
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
        return self::getOption('exceptionMonitorDomain');
    }

    /**
     * Set exceptionMonitorDomain in config.
     *
     * @param string $newExceptionMonitorDomain.
     */
    public static function setExceptionMonitorDomain(string $newExceptionMonitorDomain): void
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
        $exceptionMonitorMail = self::getOption('exceptionMonitorMail');

        return $exceptionMonitorMail;
    }

    /**
     * Set exceptionMonitorMail in config.
     *
     * @param string $newExceptionMonitorMail.
     */
    public static function setExceptionMonitorMail(string $newExceptionMonitorMail): void
    {
        self::setConfiguration([$newExceptionMonitorMail]);
    }
}
