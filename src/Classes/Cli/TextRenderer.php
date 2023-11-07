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

    public static function getPadding(array $items): int
    {
        $padding = 0;

        foreach ($items as $item) {
            $itemLength = \mb_strlen($item);
            $padding = \max($padding, $itemLength);
        }

        $padding += 1;

        return $padding;
    }
}
