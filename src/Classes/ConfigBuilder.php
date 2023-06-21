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

class ConfigBuilder
{
    public function update(string $configString, array $options)
    {
        $newConfigString = $configString;
        foreach ($options as $key => $value) {
            $newConfigString = $this->updateOption($newConfigString, $key, $value);
        }
        return $newConfigString;
    }

    private function updateOption(string $configString, string $key, $value): string
    {
        if ($this->hasEntry($configString, $key)) {
            return $this->replaceEntry($configString, $key, $value);
        } else {
            return $this->addEntry($configString, $key, $value);
        }
    }

    private function replaceEntry(string $configString, string $key, $value): string
    {
        /**
         * Look for line which matches:
         * '$key' => 'foobar' (i. e.: 'username' => 'root')
         */
        $regex = "/^[ |\t]*'$key'\s*=>\s*'.*',*/m";
        $newLine = "    '$key' => '$value',";
        return preg_replace($regex, $newLine, $configString);
    }

    private function addEntry(string $configString, string $key, $value): string
    {
        $lastEntry = $this->getLastEntry($configString);
        $newEntry = "    '$key' => '$value',";

        $lastEntryAndNewEntry = $this->addCommaIfNeeded($lastEntry) . "\n" .  $newEntry;

        return str_replace($lastEntry, $lastEntryAndNewEntry, $configString);
    }

    private function hasEntry(string $configString, string $key): bool
    {
        /**
         * Look for line which matches:
         * '$key' => 'foobar' (i. e.: 'username' => 'root')
         */
        $regex = "/^[ |\t]*'$key'\s*=>\s*'.*',*/m";
        preg_match($regex, $configString, $matches);

        if (count($matches) === 1) {
            return true;
        }

        return false;
    }

    private function getLastEntry(string $configString): string
    {
        /**
         * Look for line which matches:
         * 'xxx' => 'xxx'
         */
        $regex = "/^[ |\t]*'.*'\s*=>\s*'.*',*/m";
        preg_match_all($regex, $configString, $matches);
        return end($matches[0]);
    }

    private function addCommaIfNeeded(string $string): string
    {
        if (substr($string, -1) !== ',') {
            $string .= ',';
        }
        return $string;
    }
}
