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
use RobinTheHood\ModifiedModuleLoaderClient\Controllers\IndexController;

class App
{
    protected static $modulesDir = 'Modules';
    protected static $archivesDir = 'Archives';
    protected static $configDir = 'config';

    public static function setModulesDir(string $modulesDir): void
    {
        self::$modulesDir = $modulesDir;
    }

    public static function setArchivesDir(string $archivesDir): void
    {
        self::$archivesDir = $archivesDir;
    }

    public static function getRoot(): string
    {
        return realPath(__DIR__ . '/../../');
    }

    public static function getShopRoot(): string
    {
        return Config::getShopRoot();
    }

    public static function getSrcRoot(): string
    {
        return self::getRoot() . '/src';
    }

    public static function getLogsRoot(): string
    {
        return self::getRoot() . '/logs';
    }

    public static function getTemplatesRoot(): string
    {
        return self::getRoot() . '/src/Templates';
    }

    public static function getConfigRoot(): string
    {
        return self::getRoot() . '/' . self::$configDir;
    }

    /**
     * <MMLC-ROOT>/Archives/
     */
    public static function getArchivesRoot(): string
    {
        return self::getRoot() . '/' . self::$archivesDir;
    }

    /**
     * <MMLC-ROOT>/Modules/
     */
    public static function getModulesRoot(): string
    {
        return self::getRoot() . '/' . self::getModulesDirName();
    }

    public static function getModulesDirName(): string
    {
        return self::$modulesDir;
    }

    public static function getMmlcVersion(): string
    {
        $path = self::getRoot() . '/config/version.json';
        if (!file_exists($path)) {
            return '';
        }

        $json = file_get_contents($path);
        $version = json_decode($json);

        if (!$version) {
            return ''; // Better throw an exception
        }

        return $version->version;
    }

    public static function start(): void
    {
        $serverRequest = self::getServerRequest();

        $indexController = new IndexController($serverRequest);
        $result = $indexController->invoke();

        if (isset($result['redirect'])) {
            Redirect::redirect($result['redirect']);
            die();
        }

        header('Content-Type: text/html; charset=utf-8');
        echo $result['content'] ?? '';
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
