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

use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;

class AdminDirScanner
{
    /**
     * @return string[]
     */
    public function getAll(string $shopRootPath): array
    {
        $adminDirPaths = [];
        $directoryPaths = FileHelper::scanDir($shopRootPath, FileHelper::DIRS_ONLY, false);
        foreach ($directoryPaths as $directoryPath) {
            if ($this->isAdminDirPath($directoryPath)) {
                $adminDirPaths[] = $directoryPath;
            }
        }
        return $adminDirPaths;
    }

    private function isAdminDirPath(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        if (!is_dir($filePath)) {
            return false;
        }

        $fileName = basename($filePath);

        if (!$this->isAdminDirName($fileName)) {
            return false;
        }

        if (!$this->hasAdminDirFiles($filePath)) {
            return false;
        }

        return true;
    }

    private function isAdminDirName(string $fileName): bool
    {
        if ($fileName === 'admin') {
            return true;
        }

        if (strpos(strtolower($fileName), 'admin_') === 0) {
            return true;
        }

        return false;
    }

    private function hasAdminDirFiles(string $filePath): bool
    {
        // List of Files, that the admin dire
        $files = [
            'check_update.php'
        ];

        if (!FileHelper::containsAllFiles($files, $filePath)) {
            return false;
        }

        return true;
    }
}
