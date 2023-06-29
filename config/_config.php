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
     * You can update the passwort via GUI or follow this link:
     * https://module-loader.de/documentation.php#update-password
     */
    'username' => 'root',
    'password' => '$2y$12$h7klHtKaX4jDSHIf0KEvNeSNCCrlZFz6Fy5Y7e0wcfYu3I9W0JCIW',
    'accessToken' => '',

    /**
     * Modules for the MMLC are downloaded in this folder.
     *
     * Default: 'Modules'
     */
    'modulesLocalDir' => 'Modules',


    /**
     * The URL to the MMLS API that the MMLC can use to obtain modules and updates.
     *
     * Default: 'https://app.module-loader.de/api.php'
     */
    'remoteAddress' => 'https://app.module-loader.de/api.php',

    /**
     * You can choose between copy and link. If you are using the MMLC in a live shop, select copy. If you are
     * developing with the MMLC Module, choose link.
     *
     * Values: copy, link
     * Default: 'copy'
     */
    'installMode' => 'copy',

    /**
     * You can choose between strict and lax. With strict the dependencies of modules with a version lower than 1.0.0
     * are controlled more precisely. If some modules fail to install, you can try lax. Note that in Lex mode there is
     * a greater chance that different modules will not work well together.
     *
     * Values: lax, strict
     * Default: lax
     */
    'dependencyMode' => 'lax',


    /**
     * Should be updated to the next stable version of the MMLC or to the latest (alpha, beta, ...)
     *
     * Values: stable, latest
     * Default: 'stable'
     */
    'selfUpdate' => 'stable',

    /**
     * The MMLC can automatically find your admin directory even if it has been renamed. If that doesn't work, you can
     * enter the name of the admin directory here. Leave the field blank if you want the MMLC to automatically try to
     * find the admin directory.
     *
     * Default: ''
     */
    'adminDir' => '',

    /**
     * Settings revolving around your modified-shop
     *
     * Overwrite the default shop path. If your MMLC installation is not inside
     * of your modified-shop root and exists as a symbolic link, you may need to
     * define your shop root here.
     *
     * Default: ''
     */
    'shopRoot' => '',

    /**
     * Should (error) messages be logged in the ModifiedModuleLoaderClient/logs/ directory?
     *
     * Values: true, false
     * Default: ture
     */
    'logging' => 'true',


    /**
     * If the MMLC should display programming errors in the browser, you can enter the domain to which this applies.
     * The exception monitor becomes active in the event of errors as soon as the domain stored is the same as that
     * from which the MMLC is called. Example <code>www.example.org</code>
     *
     * Default: ''
     */
    'exceptionMonitorDomain' => '',
];
