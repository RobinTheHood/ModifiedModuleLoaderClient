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

namespace RobinTheHood\ModifiedModuleLoaderClient\Semver;

class Version
{
    protected $major;
    protected $minor;
    protected $patch;
    protected $tag;

    public function __construct(int $major, int $minor, int $patch, string $tag = '')
    {
        $this->major = $major;
        $this->minor = $minor;
        $this->patch = $patch;
        $this->tag = $tag;
    }

    public function getMajor(): int
    {
        return $this->major;
    }

    public function getMinor(): int
    {
        return $this->minor;
    }

    public function getPatch(): int
    {
        return $this->patch;
    }

    public function getTag(): string
    {
        return $this->tag;
    }
}
