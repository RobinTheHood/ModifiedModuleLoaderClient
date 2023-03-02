<?php

declare(strict_types=1);

namespace RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher;

class ChangedEntry
{
    public const TYPE_UNCHANGED = 0;
    public const TYPE_NEW = 1;
    public const TYPE_DELETED = 2;
    public const TYPE_CHANGED = 3;

    public $file;
    public $type;

    public static function createFromHashEntry(HashEntry $hashEntry, int $type): ChangedEntry
    {
        $changedEntry = new ChangedEntry();
        $changedEntry->file = $hashEntry->file;
        $changedEntry->type = $type;
        return $changedEntry;
    }
}
