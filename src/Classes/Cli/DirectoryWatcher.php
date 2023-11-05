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

namespace RobinTheHood\ModifiedModuleLoaderClient\Cli;

use Exception;

class DirectoryWatcher
{
    public const STATUS_NEW = 1;
    public const STATUS_CHANGED = 2;
    public const STATUS_DELETED = 3;

    private $directoryPath;
    private $lastFiles = [];
    private $initialized = false;

    public function init(string $directoryPath): void
    {
        $this->directoryPath = $directoryPath;
        $this->initialized = true;
        $this->scann();
    }

    public function watch(callable $callback, int $intervalInSeconds = 5): void
    {
        if (!$this->initialized) {
            throw new Exception("The 'init' method must be called first to initialize the directory.");
        }

        while (true) {
            $changes = $this->scann();
            $callback($changes);
            sleep($intervalInSeconds);
        }
    }

    private function scann(): array
    {
        $changes = [];

        $currentFiles = $this->getFilesRecursively($this->directoryPath);

        foreach ($currentFiles as $file) {
            $filePath = $file;
            $lastModifiedTime = filemtime($filePath);

            if (!isset($this->lastFiles[$filePath])) {
                $changes[$filePath] = self::STATUS_NEW;
            } elseif ($this->lastFiles[$filePath] !== $lastModifiedTime) {
                $changes[$filePath] = self::STATUS_CHANGED;
            }

            $this->lastFiles[$filePath] = $lastModifiedTime;
        }

        $deletedFiles = array_diff(array_keys($this->lastFiles), $currentFiles);

        if (!empty($deletedFiles)) {
            foreach ($deletedFiles as $file) {
                $changes[$file] = self::STATUS_DELETED;
                unset($this->lastFiles[$file]);
            }
        }

        return $changes;
    }

    private function getFilesRecursively(string $directory): array
    {
        $fileList = [];

        $files = scandir($directory);

        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                $filePath = $directory . DIRECTORY_SEPARATOR . $file;
                if (is_dir($filePath)) {
                    $fileList = array_merge($fileList, $this->getFilesRecursively($filePath));
                } else {
                    $fileList[] = $filePath;
                }
            }
        }

        return $fileList;
    }
}
