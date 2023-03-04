<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


declare(strict_types=1);

namespace RobinTheHood\ModifiedModuleLoaderClient\FileHasher;

class HashFileLoader
{
    private $defaultScope = 'root';

    public function setDefaultScope(string $scope): void
    {
        $this->defaultScope = $scope;
    }

    public function load(string $path): ?HashFile
    {
        if (!file_exists($path)) {
            return null;
        }
        $json = file_get_contents($path);
        return HashFileFactory::createFromJson($json, $this->defaultScope);
    }
}
