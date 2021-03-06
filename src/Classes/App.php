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

use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;

class App
{
    protected static $modulesDir = 'Modules';
    protected static $archivesDir = 'Archives';
    protected static $configDir = 'config';

    public static function setModulesDir($modulesDir)
    {
        self::$modulesDir = $modulesDir;
    }

    public static function setArchivesDir($archivesDir)
    {
        self::$archivesDir = $archivesDir;
    }

    public static function getRoot()
    {
        return realPath(__DIR__ . '/../../');
    }

    public static function getShopRoot()
    {
        return realPath(__DIR__ . '/../../../');
    }

    public static function getSrcRoot()
    {
        return self::getRoot() . '/src';
    }

    public static function getTemplatesRoot()
    {
        return self::getRoot() . '/src/Templates';
    }

    public static function getConfigRoot()
    {
        return self::getRoot() . '/' . self::$configDir;
    }

    public static function getArchivesRoot()
    {
        return self::getRoot() . '/' . self::$archivesDir;
    }

    public static function getModulesRoot()
    {
        return self::getRoot() . '/' . self::getModulesDirName();
    }

    public static function getModulesDirName()
    {
        return self::$modulesDir;
    }

    public static function start()
    {
        $serverRequest = self::getServerRequest();

        $indexController = new IndexController($serverRequest);
        $viewResult = $indexController->invoke();
        echo $viewResult['content'];

    }

    private static function getServerRequest(): ServerRequestInterface
    {
        $psr17Factory = new Psr17Factory();

        $creator = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );
        
        $serverRequest = $creator->fromGlobals();

        return $serverRequest;
    }
}
