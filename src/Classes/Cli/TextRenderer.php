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

namespace RobinTheHood\ModifiedModuleLoaderClient\Cli;

class TextRenderer
{
    public const COLOR_RED = 31;
    public const COLOR_GREEN = 32;
    public const COLOR_YELLOW = 33;

    public static function color(string $text, int $color): string
    {
        return "\e[" . $color . "m" . $text . "\e[0m";
    }

    public static function rightPad(string $text, int $length): string
    {
        return \str_pad($text, $length, ' ', \STR_PAD_RIGHT);
    }

    public static function getMaxLength(array $items): int
    {
        $maxLength = 0;

        foreach ($items as $item) {
            $currentLength = \mb_strlen($item);
            $maxLength = \max($maxLength, $currentLength);
        }

        return $maxLength;
    }
}
