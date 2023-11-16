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
        $strippedText = $text;
        $strippedText = self::stripEscapeSequencesColor($text);
        $strippedText = self::stripEscapeSequenceLink($strippedText);
        return $strippedText;
    }

    public static function getTextLength(string $text): int
    {
        $strippedText = self::stripEscapeSequences($text);
        $textLength = strlen($strippedText);
        return $textLength;
    }

    public static function rightPad(string $text, int $totalLength): string
    {
        $paddingLength = self::getPadLength($text, $totalLength);
        $padding = str_repeat(' ', $paddingLength);
        return $text . $padding;
    }

    public static function leftPad(string $text, int $totalLength): string
    {
        $paddingLength = self::getPadLength($text, $totalLength);
        $padding = str_repeat(' ', $paddingLength);
        return $padding . $text;
    }

    private static function getPadLength(string $text, int $totalLength): int
    {
        $textLength = self::getTextLength($text);

        if ($textLength >= $totalLength) {
            return 0;
        }

        $paddingLength = $totalLength - $textLength;
        return $paddingLength;
    }

    public static function getMaxLength(array $items): int
    {
        $maxLength = 0;

        foreach ($items as $item) {
            $currentLength = self::getTextLength($item);
            $maxLength = max($maxLength, $currentLength);
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

    public static function renderTable(array $content, array $settings): string
    {
        $columns = [];
        foreach ($content as $row) {
            foreach ($row as $columnIndex => $value) {
                $columns[$columnIndex][] = $value;
            }
        }

        $maxLengthPerColumn = [];
        foreach ($columns as $columnIndex => $column) {
            $maxLengthPerColumn[$columnIndex] = self::getMaxLength($column);
        }

        $resultRows = [];
        foreach ($content as $row) {
            $resultRow = '';
            foreach ($row as $columnIndex => $value) {
                $paddedValue = '';
                if ($settings[$columnIndex] === 'left') {
                    $paddedValue = self::rightPad($value, $maxLengthPerColumn[$columnIndex]);
                } elseif ($settings[$columnIndex] === 'right') {
                    $paddedValue = self::leftPad($value, $maxLengthPerColumn[$columnIndex]);
                }
                $resultRow .= $paddedValue;
            }
            $resultRows[] = $resultRow;
        }

        return implode("\n", $resultRows);
    }
}
