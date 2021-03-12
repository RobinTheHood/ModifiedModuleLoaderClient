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

class FileInfo
{
    public static function isTemplateFile(string $path): bool
    {
        if (strpos($path, '/templates/') === 0) {
            return true;
        }
        return false;
    }
}
