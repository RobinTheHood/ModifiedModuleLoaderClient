<?php

declare(strict_types=1);

namespace RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher;

class ChangedEntry
{
    public const TYPE_UNCHANGED = 0;
    public const TYPE_NEW = 1;
    public const TYPE_DELETED = 2;
    public const TYPE_CHANGED = 3;

    /**
     * @var string $file
     */
    public $file = '';

    /**
     * @var int $type
     */
    public $type = self::TYPE_UNCHANGED;

    /**
     * Erzeut ein ChangedEntry aus einem HashEntry
     *
     * @param int $type ChangedEntry::TYPE_...
     */
    public static function createFromHashEntry(HashEntry $hashEntry, int $type): ChangedEntry
    {
        $changedEntry = new ChangedEntry();
        $changedEntry->file = $hashEntry->file;
        $changedEntry->type = $type;
        return $changedEntry;
    }
}
