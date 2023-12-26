<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient\Helpers;

use RobinTheHood\ModifiedModuleLoaderClient\Helpers\ServerHelper;

class FileHelper
{
    public const FILES_AND_DIRS = 0;
    public const FILES_ONLY = 1;
    public const DIRS_ONLY = 2;

    /** @var String[] */
    protected static $ignoreList = [
        '.DS_Store', '.git'
    ];

    public static function scanDir($rootPath, $mode = self::FILES_AND_DIRS, $includeInvisibles = false)
    {
        return self::scanDirRecursive($rootPath, $mode, $includeInvisibles, 1);
    }

    public static function scanDirRecursive(
        $rootPath,
        $mode = self::FILES_AND_DIRS,
        $includeInvisibles = false,
        $depth = -1
    ) {
        $paths = [];

        if ($depth == 0) {
            return $paths;
        }
        $depth--;

        if (!file_exists($rootPath)) {
            return $paths;
        }

        $fileNames = scandir($rootPath);

        foreach ($fileNames as $fileName) {
            if ($fileName == '.' || $fileName == '..') {
                continue;
            }

            if (!$includeInvisibles && self::isInvisible($fileName)) {
                continue;
            }

            if (in_array($fileName, self::$ignoreList)) {
                continue;
            }

            $path = $rootPath . '/' . $fileName;

            if (is_dir($path)) {
                if ($mode != self::FILES_ONLY) {
                    $paths[] = $path;
                }

                $subPaths = self::scanDirRecursive($path, $mode, $includeInvisibles, $depth);
                $paths = array_merge($paths, $subPaths);
            } else {
                if ($mode != self::DIRS_ONLY) {
                    $paths[] = $path;
                }
            }
        }

        return $paths;
    }

    public static function isInvisible($path)
    {
        if ($path[0] == '.') {
            return true;
        }
        return false;
    }

    public static function stripAllBasePaths($basePath, $paths)
    {
        $newPaths = [];
        foreach ($paths as $path) {
            $newPaths[] = self::stripBasePath($basePath, $path);
        }
        return $newPaths;
    }

    public static function stripBasePath($basePath, $path)
    {
        return str_replace($basePath, '', $path);
    }

    public static function makeDirIfNotExists($path)
    {
        $path = dirname($path);
        if (!file_exists($path)) {
            self::makeDirIfNotExists($path);
            mkdir($path);
        }
    }

    public static function moveFilesTo($filePaths, $oldBasePath, $newBasePath, $exclude = [])
    {
        foreach ($filePaths as $filePath) {
            $relativeFilePath = FileHelper::stripBasePath($oldBasePath, $filePath);
            $newFilePath = $newBasePath . $relativeFilePath;

            if (in_array($relativeFilePath, $exclude)) {
                continue;
            }

            if (!(\file_exists($filePath) && !\file_exists($newFilePath))) {
                continue;
            }

            rename($filePath, $newFilePath);
        }
    }

    public static function readFile($path, $isHttp = false)
    {
        if ($isHttp || strpos($path, 'http://') !== false || strpos($path, 'https://') !== false) {
            if (!ServerHelper::urlExists($path)) {
                return '';
            }
        }

        return @file_get_contents($path);
    }

    public static function readMarkdown($path, $isHttp = false)
    {
        $fileContent = self::readFile($path, $isHttp);
        if ($fileContent) {
            $parsedown = new \Parsedown();
            $html = $parsedown->text($fileContent);
            return $html;
        }
        return '';
    }

    public static function containsAllFiles($files, $baseDirectory): bool
    {
        foreach ($files as $file) {
            if (!\file_exists($baseDirectory . '/' . $file)) {
                return false;
            }
        }
        return true;
    }

    public static function deletePathIsEmpty(string $path): void
    {
        if (file_exists($path) && !is_dir($path)) {
            return;
        }

        if (file_exists($path) && is_dir($path) && !self::isDirEmpty($path)) {
            return;
        }

        if (file_exists($path) && is_dir($path) && self::isDirEmpty($path)) {
            rmdir($path);
        }

        self::deletePathIsEmpty(dirname($path));
    }

    public static function isDirEmpty(string $path): bool
    {
        if (!is_readable($path)) {
            return false;
        }
        return (count(scandir($path)) === 2);
    }
}
