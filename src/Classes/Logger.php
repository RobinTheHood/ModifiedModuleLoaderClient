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

class Logger
{
    /** @var string $logDir */
    private $logDir;

    /** @var bool $logging */
    private $logging = true;

    public static function staticLog(string $logLevel, string $message)
    {
        $logger = new Logger();
        $logger->setLogDir(App::getLogsRoot() . '/');
        $logger->log($logLevel, $message);
    }

    public function setLogDir(string $logDir): void
    {
        $this->logDir = $logDir;
    }

    public function log(string $logLevel, string $message): void
    {
        if (!$this->logging) {
            return;
        }

        $logEntry = $this->createLogEntry($logLevel, $message);
        $this->writeLogEntry($logEntry);
    }

    private function createLogEntry(string $logLevel, string $message): string
    {
        $currentDateTime = date('Y-m-d H:i:s');

        $logEntry = [
            'timestamp' => $currentDateTime,
            'level' => $logLevel,
            'message' => $message
        ];

        return json_encode($logEntry, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    private function writeLogEntry(string $message): void
    {
        if (!file_exists($this->logDir)) {
            mkdir($this->logDir);
        }

        $fileName = date('Y-m-d') . '.log';
        $filePath = $this->logDir . $fileName;

        file_put_contents($filePath, $message, FILE_APPEND);
    }
}
