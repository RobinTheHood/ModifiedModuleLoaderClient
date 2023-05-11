<?php

namespace RobinTheHood\ModifiedModuleLoaderClient\Logger;

/**
 * Describes log levels.
 */
class LogLevel
{
    public const EMERGENCY = 'emergency';
    public const ALERT     = 'alert';
    public const CRITICAL  = 'critical';
    public const ERROR     = 'error';
    public const WARNING   = 'warning';
    public const NOTICE    = 'notice';
    public const INFO      = 'info';
    public const DEBUG     = 'debug';

    public static function toString(string $level): string
    {
        $levels = [
            self::EMERGENCY => 'EMERGENCY',
            self::ALERT => 'ALERT',
            self::CRITICAL => 'CRITICAL',
            self::ERROR => 'ERROR',
            self::WARNING => 'WARNING',
            self::NOTICE => 'NOTICE',
            self::INFO => 'INFO',
            self::DEBUG => 'DEBUG'
        ];

        return $levels[$level] ?? 'NO_LEVEl';
    }
}
