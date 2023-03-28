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

namespace RobinTheHood\ModifiedModuleLoaderClient\DependencyManager;

class SystemSet
{
    /**
     * SystemNames: mmlc, modified, php
     *
     * An array entry looks like
     * array<archiveName, versionString>
     * or
     * array<systemName, versionString>
     *
     * @var array<string, string>
     **/
    private $systems = [];

    /**
     * @param array<string, string> $sytems
     */
    public function set(array $sytems): void
    {
        $this->systems = $sytems;
    }

    public function add(string $name, string $versionString): void
    {
        $this->systems[$name] = $versionString;
    }

    /**
     * @return string[]
     */
    public function getArchiveNames(): array
    {
        $archiveNames = array_values(array_filter(array_keys($this->systems), function ($name) {
            return strpos($name, '/') !== false;
        }));
        return $archiveNames;
    }

    /**
     * @return array<string, string>
     */
    public function getAll(): array
    {
        return $this->systems;
    }

    /**
     * @return array<string, string>
     */
    public function getArchives(): array
    {
        $archives = [];
        foreach ($this->systems as $name => $version) {
            if (!strpos($name, '/')) {
                continue;
            }
            $archives[$name] = $version;
        }
        return $archives;
    }

    public function remove(string $name): void
    {
        if (!array_key_exists($name, $this->systems)) {
            return;
        }

        unset($this->systems[$name]);
    }
}
