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

namespace RobinTheHood\ModifiedModuleLoaderClient\Archive;

/**
 * Eine VO (Value Object) Klasse
 *
 * Da der Name eines Archives nicht willkührlich gewählt sein darf, wir der Name eines Archives durch diese Klasse
 * repräsentiert. Der Konstruktor kann beim Erstellen eines Objekts zudem überprüfen, ob es sich um einen validen
 * ArchiveNamen-String handelt.
 */
class ArchiveName
{
    /** @var string */
    private $value;

    /** @var string */
    private $vendorName;

    /** @var string */
    private $moduleName;

    public function __construct(string $value)
    {
        if (!$this->isValidArchiveName($value)) {
            throw new \InvalidArgumentException('No valid ArchiveName');
        }

        $this->value = $value;

        $parts = explode('/', $value);
        $this->vendorName = $parts[0];
        $this->moduleName = $parts[1];
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function getVendorName(): string
    {
        return $this->vendorName;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private function isValidArchiveName(string $archiveName): bool
    {
        $pattern = '/^([A-Za-z0-9_-]+)\/([A-Za-z0-9_-]+)$/';

        return preg_match($pattern, $archiveName) === 1;
    }
}
