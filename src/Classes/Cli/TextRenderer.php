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

    public static function rightPad(string $text, int $totalLength): string
    {
        $textLength = strlen($text);

        if ($textLength >= $totalLength) {
            return $text;
        }

        $paddingLength = $totalLength - $textLength;
        $padding = str_repeat(' ', $paddingLength);

        return $text . $padding;
    }

    public static function renderHelpHeading(string $heading): string
    {
        return self::color($heading, self::COLOR_YELLOW) . "\n";
    }

    public static function renderHelpCommand(string $name, string $description, int $pad = 20): string
    {
        $name = self::rightPad($name, $pad);
        return "  " . self::color($name, self::COLOR_GREEN) . " $description\n";
    }

    public static function renderHelpArgument(string $name, string $description, int $pad = 20): string
    {
        $name = self::rightPad($name, $pad);
        return "  " . self::color($name, self::COLOR_GREEN) . " $description\n";
    }

    public static function renderHelpOption(string $shortName, string $longName, string $description, int $pad = 20): string
    {
        $name = '';

        if ($shortName && $longName) {
            $name = "-$shortName, --$longName";
        } elseif ($shortName) {
            $name = "-$shortName";
        } elseif ($longName) {
            $name = "    --$longName";
        }

        $name = self::rightPad($name, $pad);
        return "  " . self::color($name, self::COLOR_GREEN) . " $description\n";
    }
}
