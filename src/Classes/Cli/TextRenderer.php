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

    public static function moduleLink(string $archiveName, string $titel): string
    {
        return self::link('https://module-loader.de/modules/' . $archiveName, $titel);
    }

    public static function link(string $url, string $titel): string
    {
        return "\e]8;;$url\e\\$titel\e]8;;\e\\";
    }

    public static function color(string $text, int $color): string
    {
        return "\e[" . $color . "m" . $text . "\e[0m";
    }

    public static function stripEscapeSequencesColor(string $text): string
    {
        // Muster für Escape-Sequenzen finden und ersetzen
        $pattern = '/\\e\[\d+m(.*?)\\e\[0m/';
        $replacement = '$1';

        // Escape-Sequenzen filtern
        $filteredString = preg_replace($pattern, $replacement, $text);

        return $filteredString;
    }

    public static function stripEscapeSequenceLink(string $text): string
    {
        // Muster für Escape-Sequenzen finden und ersetzen
        $pattern = '/\\e\]8;;(.*?)\\e\\\\(.*?)\\e\]8;;\\e\\\\/';
        $replacement = '$2';

        // Escape-Sequenzen filtern
        $filteredString = preg_replace($pattern, $replacement, $text);

        return $filteredString;
    }

    public static function stripEscapeSequences(string $text): string
    {
        $strippedText = self::stripEscapeSequencesColor($text);
        return self::stripEscapeSequenceLink($strippedText);
    }

    public static function getTextLength(string $text): int
    {
        $strippedText = self::stripEscapeSequences($text);
        $textLength = strlen($strippedText);
        return $textLength;
    }

    public static function rightPad(string $text, int $totalLength): string
    {
        $textLength = self::getTextLength($text);

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
