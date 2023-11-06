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

    public static function renderHelpHeading(string $heading): void
    {
        //echo "\e[33m$heading\e[0m\n";
        echo self::color($heading, self::COLOR_YELLOW) . "\n";
    }

    public static function renderHelpCommand(string $name, string $description, int $pad = 20)
    {
        $name = self::rightPad($name, 20);
        echo "  " . self::color($name, self::COLOR_GREEN) . " $description\n";
    }

    public static function renderLogo()
    {
        // echo "    __  _____  _____    ______   ________    ____\n";
        // echo "   /  |/  /  |/  / /   / ____/  / ____/ /   /  _/\n";
        // echo "  / /|_/ / /|_/ / /   / /      / /   / /    / /  \n";
        // echo " / /  / / /  / / /___/ /___   / /___/ /____/ /   \n";
        // echo "/_/  /_/_/  /_/_____/\____/   \____/_____/___/   \n";
        // created with: https://patorjk.com/software/taag/#p=display&f=Slant&t=MMLC%20CLI

        echo "    __  ___ __  ___ __    ______   ______ __     ____\n";
        echo "   /  |/  //  |/  // /   / ____/  / ____// /    /  _/\n";
        echo "  / /|_/ // /|_/ // /   / /      / /    / /     / /  \n";
        echo " / /  / // /  / // /___/ /___   / /___ / /___ _/ /   \n";
        echo "/_/  /_//_/  /_//_____/\____/   \____//_____//___/   \n";
        // cretated with: https://patorjk.com/software/taag/#p=display&h=1&f=Slant&t=MMLC%20CLI
    }
}
