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
    /** @var array<string,string> */
    public $systems = [];

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

    public function removeByArchiveName(string $archiveName): void
    {
        if (array_key_exists($archiveName, $this->systems)) {
            unset($this->systems[$archiveName]);
        }
    }
}
