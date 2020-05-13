<?php

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
    public static function isTemplateFile($path)
    {
        if (strpos($path, '/templates/') === 0) {
            return true;
        } else {
            return false;
        }
    }
}
