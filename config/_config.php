<?php
/**
 * For more information about this config.php file and options visit:
 * @link https://module-loader.de/docs/config_config.php
 */

$configuration = [
    /**
     * User specific settings used for logging in.
     *
     * The default password is 'root'. This will be overwritten when you create a new account.
     */
    'username' => 'root',
    'password' => '$2y$12$h7klHtKaX4jDSHIf0KEvNeSNCCrlZFz6Fy5Y7e0wcfYu3I9W0JCIW',
    'accessToken' => '',

    'modulesLocalDir' => 'Modules',
    'remoteAddress' => 'https://app.module-loader.de/api.php',

    /**
     * Settings revolving around MMLC itself.
     *
     * @param string installMode copy
     * @param string selfUpdate stable, latest
     */
    'installMode' => 'copy',
    'selfUpdate' => 'stable'
];
