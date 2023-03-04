<?php

declare(strict_types=1);

namespace RobinTheHood\ModifiedModuleLoaderClient\FileHasher;

class ChangedEntry
{
    public const TYPE_UNCHANGED = 0;
    public const TYPE_NEW = 1;
    public const TYPE_DELETED = 2;
    public const TYPE_CHANGED = 3;

    /**
     * @var HashEntry $hashEntryA
     */
    public $hashEntryA;

    /**
     * @var HashEntry $hashEntryB
     */
    public $hashEntryB;

    /**
     * @var int $type
     */
    public $type = self::TYPE_UNCHANGED;

    /**
     * Erzeut ein ChangedEntry aus einem HashEntry
     *
     * @param int $type ChangedEntry::TYPE_...
     */
    public static function createFromHashEntry(int $type, HashEntry $hashEntryA, ?HashEntry $hashEntryB): ChangedEntry
    {
        $changedEntry = new ChangedEntry();
        $changedEntry->hashEntryA = $hashEntryA;
        if ($hashEntryB) {
            $changedEntry->hashEntryB = $hashEntryB;
        }
        $changedEntry->type = $type;
        return $changedEntry;
    }

    public function clone(): ChangedEntry
    {
        $changedEntry = new ChangedEntry();
        $changedEntry->hashEntryA = $this->hashEntryA->clone();
        if ($this->hashEntryB) {
            $changedEntry->hashEntryB = $this->hashEntryB->clone();
        }
        $changedEntry->type = $this->type;
        return $changedEntry;
    }
}
