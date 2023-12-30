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

namespace RobinTheHood\ModifiedModuleLoaderClient\ModuleManager;

use ArrayIterator;
use Exception;
use IteratorAggregate;
use RobinTheHood\ModifiedModuleLoaderClient\Module;

class AutoloadEntryCollection implements IteratorAggregate
{
    /**
     * @var AutoloadEntry[] $autoloadEntries
     */
    private $autoloadEntries = [];

    public static function createFromModule(Module $module): AutoloadEntryCollection
    {
        $collection = new AutoloadEntryCollection();

        $autoload = $module->getAutoload();

        if (!$autoload) {
            return $collection;
        }

        $psr4Autoload = $autoload['psr-4'] ?? [];
        if (!$psr4Autoload) {
            return $collection;
        }

        foreach ($psr4Autoload as $namespace => $path) {
            $autoloadEntry = AutoloadEntry::createFromModule($module, $namespace, $path);
            $collection->add($autoloadEntry);
        }

        return $collection;
    }

    /**
     * @param Module[] $modules
     */
    public static function createFromModules(array $modules): AutoloadEntryCollection
    {
        $resultCollection = new AutoloadEntryCollection();
        foreach ($modules as $module) {
            $collection = AutoloadEntryCollection::createFromModule($module);
            $resultCollection = $resultCollection->mergeWith($collection);
        }
        return $resultCollection;
    }

    /**
     * Get all AutoloadEntry objects in the collection.
     *
     * @return AutoloadEntry[]
     */
    public function getAutoloadEntries(): array
    {
        return $this->autoloadEntries;
    }

    public function add(AutoloadEntry $autoloadEntry): void
    {
        $this->validateNamespace($autoloadEntry);
        $this->validateRealPath($autoloadEntry);

        $this->autoloadEntries[] = $autoloadEntry;
    }

    public function mergeWith(AutoloadEntryCollection $otherCollection): AutoloadEntryCollection
    {
        $mergedCollection = new AutoloadEntryCollection();
        $mergedCollection->autoloadEntries = array_merge($this->autoloadEntries, $otherCollection->autoloadEntries);
        return $mergedCollection;
    }

    /**
     * Get an AutoloadEntry by namespace.
     *
     * @param string $namespace
     * @return AutoloadEntry|null
     */
    public function getEntryByNamespace(string $namespace): ?AutoloadEntry
    {
        foreach ($this->autoloadEntries as $autoloadEntry) {
            if ($autoloadEntry->namespace === $namespace) {
                return $autoloadEntry;
            }
        }

        return null;
    }

    /**
     * Get an AutoloadEntry by path.
     *
     * @param string $path
     * @return AutoloadEntry|null
     */
    public function getEntryByRealPath(string $realPath): ?AutoloadEntry
    {
        foreach ($this->autoloadEntries as $autoloadEntry) {
            if ($autoloadEntry->realPath === $realPath) {
                return $autoloadEntry;
            }
        }

        return null;
    }

    /**
     * Get a new AutoloadEntryCollection with unique namespaces.
     *
     * @return AutoloadEntryCollection
     */
    public function unique(): AutoloadEntryCollection
    {
        $uniqueCollection = new AutoloadEntryCollection();

        foreach ($this->autoloadEntries as $autoloadEntry) {
            $existingEntry = $uniqueCollection->getEntryByNamespace($autoloadEntry->namespace);

            if ($existingEntry) {
                continue;
            }

            $uniqueCollection->add($autoloadEntry);
        }

        return $uniqueCollection;
    }

    /**
     * Get the iterator for iterating over AutoloadEntry objects.
     *
     * @return ArrayIterator<int, AutoloadEntry>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->autoloadEntries);
    }

    private function validateNamespace(AutoloadEntry $autoloadEntry): void
    {
        $foundAutoloadEntry = $this->getEntryByNamespace($autoloadEntry->namespace);

        if (!$foundAutoloadEntry) {
            return;
        }

        if ($foundAutoloadEntry->realPath === $autoloadEntry->realPath) {
            return;
        }

        $archiveName = 'unknown module';
        if ($autoloadEntry->module) {
            $archiveName = $autoloadEntry->module->getArchiveName() . ':' . $autoloadEntry->module->getVersion();
        }

        throw new Exception(
            "Can not add $archiveName because"
            . " Namespace {$autoloadEntry->namespace} with path {$autoloadEntry->realPath} already exists with"
            . " different path {$foundAutoloadEntry->realPath}"
        );
    }

    private function validateRealPath(AutoloadEntry $autoloadEntry): void
    {
        $foundAutoloadEntry = $this->getEntryByRealPath($autoloadEntry->realPath);

        if (!$foundAutoloadEntry) {
            return;
        }

        if ($foundAutoloadEntry->namespace === $autoloadEntry->namespace) {
            return;
        }

        $archiveName = 'unknown module';
        if ($autoloadEntry->module) {
            $archiveName = $autoloadEntry->module->getArchiveName() . ':' . $autoloadEntry->module->getVersion() ;
        }

        throw new Exception(
            "Can not add $archiveName because"
            . " path {$autoloadEntry->realPath} with namespace {$autoloadEntry->namespace} already exists with"
            . " different namespace {$foundAutoloadEntry->namespace}"
        );
    }
}
