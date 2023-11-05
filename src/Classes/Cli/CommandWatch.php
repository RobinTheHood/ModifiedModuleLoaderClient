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

use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\FileHelper;

class CommandWatch
{
    public function __construct()
    {
    }

    public function run()
    {
        $basePath = App::getModulesRoot();
        $directory = App::getModulesRoot();

        echo "Watching the directory \033[32mModules\033[0m ...\n";

        $dircectoryWatcher = new DirectoryWatcher();
        $dircectoryWatcher->init($directory);
        $dircectoryWatcher->watch(function ($changes) use ($basePath) {
            if (!$changes) {
                return;
            }

            foreach ($changes as $filePath => $status) {
                $relativeFilePath = FileHelper::stripBasePath($basePath, $filePath);

                if ($status === DirectoryWatcher::STATUS_NEW) {
                    echo "\033[32mFile added:\033[0m $relativeFilePath\n";
                } elseif ($status === DirectoryWatcher::STATUS_CHANGED) {
                    echo "\033[33mFile modified:\033[0m $relativeFilePath\n";
                } elseif ($status === DirectoryWatcher::STATUS_DELETED) {
                    echo "\033[31mFile deleted:\033[0m $relativeFilePath\n";
                }
            }
        });
    }
}
