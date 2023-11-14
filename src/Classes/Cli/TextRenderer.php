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

    /**
     * @deprecated 1.21.0 Use a `HelpRenderer` instead.
     *
     * @param string $heading
     *
     * @return string
     */
    public static function renderHelpHeading(string $heading): string
    {
        return self::color($heading, self::COLOR_YELLOW) . "\n";
    }

    /**
     * @deprecated 1.21.0 Use a `HelpRenderer` instead.
     *
     * @param string $name
     * @param string $description
     * @param int $pad
     *
     * @return string
     */
    public static function renderHelpCommand(string $name, string $description, int $pad = 20): string
    {
        $name = self::rightPad($name, $pad);
        return "  " . self::color($name, self::COLOR_GREEN) . " $description\n";
    }

    /**
     * @deprecated 1.21.0 Use a `HelpRenderer` instead.
     *
     * @param string $name
     * @param string $description
     * @param int $pad
     *
     * @return string
     */
    public static function renderHelpArgument(string $name, string $description, int $pad = 20): string
    {
        $name = self::rightPad($name, $pad);
        return "  " . self::color($name, self::COLOR_GREEN) . " $description\n";
    }

    /**
     * @deprecated 1.21.0 Use a `HelpRenderer` instead.
     *
     * @param string $shortName
     * @param string $longName
     * @param string $description
     * @param int $pad
     *
     * @return string
     */
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
