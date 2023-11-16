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

/**
 * Class TextRenderer
 *
 * A utility class for rendering text with various formatting options.
 *
 * @package RobinTheHood\ModifiedModuleLoaderClient\Cli
 */
class TextRenderer
{
    /**
     * ANSI color code for red.
     */
    public const COLOR_RED = 31;

    /**
     * ANSI color code for green.
     */
    public const COLOR_GREEN = 32;

    /**
     * ANSI color code for yellow.
     */
    public const COLOR_YELLOW = 33;

    /**
     * Generate a module link with a given archive name and title.
     *
     * @param string $archiveName The name of the module archive.
     * @param string $title The title of the module link.
     * @return string The generated module link.
     */
    public static function moduleLink(string $archiveName, string $titel): string
    {
        return self::link('https://module-loader.de/modules/' . $archiveName, $titel);
    }

    /**
     * Generate a link with a given URL and title.
     *
     * @param string $url The URL for the link.
     * @param string $title The title of the link.
     * @return string The generated link.
     */
    public static function link(string $url, string $titel): string
    {
        return "\e]8;;$url\e\\$titel\e]8;;\e\\";
    }

    /**
     * Apply color to a given text using ANSI color codes.
     *
     * @param string $text The text to be colored.
     * @param int $color The ANSI color code.
     * @return string The colored text.
     */
    public static function color(string $text, int $color): string
    {
        return "\e[" . $color . "m" . $text . "\e[0m";
    }

    /**
     * Remove ANSI escape sequences related to colors from a given text.
     *
     * @param string $text The text with escape sequences.
     * @return string The text with escape sequences removed.
     */
    public static function stripEscapeSequencesColor(string $text): string
    {
        // Muster für Escape-Sequenzen finden und ersetzen
        $pattern = '/\\e\[\d+m(.*?)\\e\[0m/';
        $replacement = '$1';

        // Escape-Sequenzen filtern
        $filteredString = preg_replace($pattern, $replacement, $text);

        return $filteredString;
    }

    /**
     * Remove ANSI escape sequences related to links from a given text.
     *
     * @param string $text The text with escape sequences.
     * @return string The text with escape sequences removed.
     */
    public static function stripEscapeSequenceLink(string $text): string
    {
        // Muster für Escape-Sequenzen finden und ersetzen
        $pattern = '/\\e\]8;;(.*?)\\e\\\\(.*?)\\e\]8;;\\e\\\\/';
        $replacement = '$2';

        // Escape-Sequenzen filtern
        $filteredString = preg_replace($pattern, $replacement, $text);

        return $filteredString;
    }

    /**
     * Remove both color and link-related ANSI escape sequences from a given text.
     *
     * @param string $text The text with escape sequences.
     * @return string The text with escape sequences removed.
     */
    public static function stripEscapeSequences(string $text): string
    {
        $strippedText = $text;
        $strippedText = self::stripEscapeSequencesColor($text);
        $strippedText = self::stripEscapeSequenceLink($strippedText);
        return $strippedText;
    }

    /**
     * Get the length of the text without ANSI escape sequences.
     *
     * @param string $text The text with or without escape sequences.
     * @return int The length of the text without escape sequences.
     */
    public static function getTextLength(string $text): int
    {
        $strippedText = self::stripEscapeSequences($text);
        $textLength = strlen($strippedText);
        return $textLength;
    }

    /**
     * Right pad a text with spaces to meet the specified total length.
     *
     * @param string $text The text to be padded.
     * @param int $totalLength The desired total length.
     * @return string The padded text.
     */
    public static function rightPad(string $text, int $totalLength): string
    {
        $paddingLength = self::getPadLength($text, $totalLength);
        $padding = str_repeat(' ', $paddingLength);
        return $text . $padding;
    }

    /**
     * Left pad a text with spaces to meet the specified total length.
     *
     * @param string $text The text to be padded.
     * @param int $totalLength The desired total length.
     * @return string The padded text.
     */
    public static function leftPad(string $text, int $totalLength): string
    {
        $paddingLength = self::getPadLength($text, $totalLength);
        $padding = str_repeat(' ', $paddingLength);
        return $padding . $text;
    }

    /**
     * Get the padding length required to meet the specified total length.
     *
     * @param string $text The text to be padded.
     * @param int $totalLength The desired total length.
     * @return int The calculated padding length.
     */
    private static function getPadLength(string $text, int $totalLength): int
    {
        $textLength = self::getTextLength($text);

        if ($textLength >= $totalLength) {
            return 0;
        }

        $paddingLength = $totalLength - $textLength;
        return $paddingLength;
    }

    /**
     * Get the maximum length among an array of strings.
     *
     * @param string[] $items An array of strings.
     * @return int The maximum length among the strings.
     */
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
     * Render a heading for help information (deprecated).
     *
     * @deprecated 1.21.0 Use a `HelpRenderer` instead.
     *
     * @param string $heading The heading text.
     * @return string The rendered heading.
     */
    public static function renderHelpHeading(string $heading): string
    {
        return self::color($heading, self::COLOR_YELLOW) . "\n";
    }

    /**
     * Render a command for help information (deprecated).
     *
     * @deprecated 1.21.0 Use a `HelpRenderer` instead.
     *
     * @param string $name The command name.
     * @param string $description The command description.
     * @param int $pad The padding length.
     * @return string The rendered command.
     */
    public static function renderHelpCommand(string $name, string $description, int $pad = 20): string
    {
        $name = self::rightPad($name, $pad);
        return "  " . self::color($name, self::COLOR_GREEN) . " $description\n";
    }

    /**
     * Render an argument for help information (deprecated).
     *
     * @deprecated 1.21.0 Use a `HelpRenderer` instead.
     *
     * @param string $name The argument name.
     * @param string $description The argument description.
     * @param int $pad The padding length.
     * @return string The rendered argument.
     */
    public static function renderHelpArgument(string $name, string $description, int $pad = 20): string
    {
        $name = self::rightPad($name, $pad);
        return "  " . self::color($name, self::COLOR_GREEN) . " $description\n";
    }

    /**
     * Render an option for help information (deprecated).
     *
     * @deprecated 1.21.0 Use a `HelpRenderer` instead.
     *
     * @param string $shortName The short option name.
     * @param string $longName The long option name.
     * @param string $description The option description.
     * @param int $pad The padding length.
     * @return string The rendered option.
     */
    public static function renderHelpOption(
        string $shortName,
        string $longName,
        string $description,
        int $pad = 20
    ): string {
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

    /**
     * Render a table with the given content and settings.
     *
     * @param array $content The content of the table.
     * @param array $settings The settings for each column (left or right alignment).
     * @return string The rendered table.
     */
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
