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

namespace RobinTheHood\ModifiedModuleLoaderClient\Helpers;

use RobinTheHood\ModifiedModuleLoaderClient\App;

class TemplateHelper
{
    public static function loadStyleSheet(string $stylesheetPath): string
    {
        $absPath = App::getRoot() . '/' . $stylesheetPath;
        if (!file_exists($absPath)) {
            return '';
        }

        $link = '<link>';
        $attributes = [
            'rel' => 'stylesheet',
            'type' => 'text/css',
            'href' => $stylesheetPath . '?v=' . hash('crc32', $absPath),
        ];
        $attributesToString = [];

        foreach ($attributes as $key => $value) {
            $attributesToString[] = $key . '="' . $value . '"';

            $link = '<link ' . implode(' ', $attributesToString) . '>';
        }

        return $link;
    }
}
