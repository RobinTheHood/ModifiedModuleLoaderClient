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

    /** @var string */
    private $directoryPath;

    /** @var array */
    private $lastFiles = [];

    /** @var bool */
    private $initialized = false;

    /** @var array */
    private $changes = [];

    /** @var string[] */
    private $excludeDirs = [];

    public function init(string $directoryPath): void
    {
        $this->directoryPath = $directoryPath;
        $this->initialized = true;
        $this->scann();
    }

    public function reset()
    {
        $this->scann();
    }

    public function addExcludeDir(string $dir)
    {
        $this->excludeDirs[] = $dir;
    }

    public function watch(callable $callback, int $intervalInSeconds = 5): void
    {
        $intervalInSecondsPositiv = max(0, $intervalInSeconds);

        if (!$this->initialized) {
            throw new Exception("The 'init' method must be called first to initialize the directory.");
        }

        while (true) {
            $this->scann();
            $callback($this);
            sleep($intervalInSecondsPositiv);
        }
    }

    public function getChanges(): array
    {
        return $this->changes;
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

        $this->changes = $changes;
        return $changes;
    }

    private function isExcludeDir(string $dir): bool
    {
        foreach ($this->excludeDirs as $excludeDir) {
            if ($excludeDir === $dir) {
                return true;
            }
        }
        return false;
    }

    private function getFilesRecursively(string $directory): array
    {
        $fileList = [];

        $files = scandir($directory);

        foreach ($files as $file) {
            if ($file == "." || $file == "..") {
                continue;
            }

            if ($this->isExcludeDir($file)) {
                continue;
            }


            $filePath = $directory . DIRECTORY_SEPARATOR . $file;
            if (is_dir($filePath)) {
                $fileList = array_merge($fileList, $this->getFilesRecursively($filePath));
            } else {
                $fileList[] = $filePath;
            }
        }

        return $fileList;
    }
}
