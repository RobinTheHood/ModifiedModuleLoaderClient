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

class AccessFileCreator
{
    public function renewAccessFiles(): void
    {
        $this->checkAndCreateAccess(App::getConfigRoot(), []);
        $this->checkAndCreateAccess(App::getArchivesRoot(), []);
        $this->checkAndCreateAccess(App::getSrcRoot() . '/Classes', []);
        $this->checkAndCreateAccess(App::getModulesRoot(), ['png', 'jpg', 'gif']);

        $this->checkAndCreateAccess(
            App::getShopRoot() . '/vendor',
            ['png', 'jpg', 'gif', 'css', 'js', 'svg']
        );

        $this->checkAndCreateAccess(
            App::getShopRoot() . '/vendor-no-composer',
            ['png', 'jpg', 'gif', 'css', 'js', 'svg']
        );
    }

    /**
     * @param array<string> $fileSuffixes
     */
    private function checkAndCreateAccess(string $path, array $fileSuffixes): void
    {
        if (!file_exists($path)) {
            return;
        }

        if (!is_dir($path)) {
            return;
        }

        if (file_exists($path . '/.htaccess')) {
            return;
        }

        $this->createAccess($path . '/.htaccess', $fileSuffixes);
    }

    /**
     * @param array<string> $fileSuffixes
     */
    private function createAccess(string $path, array $fileSuffixes): void
    {
        $suffixes = '';
        $count = 1;
        foreach ($fileSuffixes as $fileSuffix) {
            $suffixes .= $fileSuffix;
            if ($count++ < count($fileSuffixes)) {
                $suffixes .= '|';
            }
        }

        $str = 'Order Allow,Deny' . "\n";
        $str .= '<FilesMatch ".*\.(' . $suffixes . ')$">' . "\n";
        $str .= '    Allow from all' . "\n";
        $str .= '</FilesMatch>';

        if (!$fileSuffixes) {
            $str = 'Order Allow,Deny';
        }

        file_put_contents($path, $str);
    }
}
