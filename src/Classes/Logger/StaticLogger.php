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

namespace RobinTheHood\ModifiedModuleLoaderClient\Logger;

use RobinTheHood\ModifiedModuleLoaderClient\App;

class StaticLogger
{
    /** @var bool $logging */
    private static $logging = true;

    public static function log(string $logLevel, string $message, array $context = []): void
    {
        if (self::$logging === false) {
            return;
        }

        $logger = new Logger();
        $logger->setLogDir(App::getLogsRoot() . '/');
        $logger->log($logLevel, $message);
    }
}
