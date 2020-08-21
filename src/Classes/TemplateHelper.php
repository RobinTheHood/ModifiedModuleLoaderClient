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

class TemplateHelper
{
    public static function loadStyleSheet(string $stylesheetPath): string
    {
        $link = '<link>';
        $attributes = [
            'rel' => 'stylesheet',
            'type' => 'text/css',
            'href' => $stylesheetPath . '?v' . hash('crc32', $stylesheetPath),
        ];
        $attributesToString = [];

        foreach ($attributes as $key => $value) {
            $attributesToString[] = $key . '="' . $value . '"';

            $link = '<link ' . implode(' ', $attributesToString) . '>';
        }

        return $link;
    }
}
