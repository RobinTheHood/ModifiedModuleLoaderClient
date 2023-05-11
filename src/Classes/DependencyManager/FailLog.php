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

class FailLog
{
    public const TYPE_UNFAIL = 0;
    public const TYPE_FAIL = 1;

    /** @var array */
    private $entries = [];

    /**
     * @param ModuleTree[] $moduleTreeChain
     */
    public function unfail(
        array $moduleTreeChain,
        string $archiveName,
        string $version = '',
        string $constraint = ''
    ): void {
        $key = $this->createKey($moduleTreeChain, $archiveName);

        $this->entries[$key] = [
            'type' => self::TYPE_UNFAIL,
            'moduleTreeChain' => $moduleTreeChain,
            'archiveName' => $version,
            'version' => $version,
            'constraint' => $constraint
        ];
    }

    /**
     * @param ModuleTree[] $moduleTreeChain
     */
    public function fail(
        array $moduleTreeChain,
        string $archiveName,
        string $version = '',
        string $constraint = ''
    ): void {
        $key = $this->createKey($moduleTreeChain, $archiveName);

        if (!array_key_exists($key, $this->entries)) {
            $this->entries[$key] = [
                'moduleTreeChain' => $moduleTreeChain,
                'type' => self::TYPE_FAIL,
                'archiveName' => $version,
                'version' => $version,
                'constraint' => $constraint
            ];
        }
    }

    /**
     * @param ModuleTree[] $moduleTreeChain
     */
    private function createKey(array $moduleTreeChain, string $archiveName): string
    {
        $key = '';
        foreach ($moduleTreeChain as $moduleTree) {
            if ($key) {
                $key .= ' > ';
            }
            $key .= $moduleTree->archiveName;
        }
        return $key . ' > ' . $archiveName;
    }

    public function __toString(): string
    {
        $strings = [];
        foreach ($this->entries as $key => $entry) {
            if ($entry['type'] !== self::TYPE_FAIL) {
                continue;
            }
            $constraint = $entry['constraint'];
            $version = $entry['version'];
            if ($constraint) {
                $strings[] = "$key $constraint";
            } else {
                $strings[] = "$key $version";
            }
        }
        return implode('<br>', $strings);
    }
}
