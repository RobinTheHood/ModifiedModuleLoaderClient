<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('LOADED_FROM_INDEX', true);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php';

use RobinTheHood\Debug\Debug;
use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\Config;
use RobinTheHood\ModifiedModuleLoaderClient\ExceptionMonitorFactory;

$exceptionMonitor = ExceptionMonitorFactory::getExceptionMonitor();
$exceptionMonitor->register();

function debugOut($value)
{
    Debug::out($value);
}

function debugDie($value)
{
    Debug::out($value);
    die();
}

function debugLog($value)
{
    $str = date('Y-m-d H:i:s');
    $str .= ': ' . print_r($value, true) . "\n";
    file_put_contents(App::getRoot() . '/log.txt', $str, FILE_APPEND);
}

App::setModulesDir(Config::getModulesLocalDir());
App::start();
