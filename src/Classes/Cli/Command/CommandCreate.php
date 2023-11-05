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

namespace RobinTheHood\ModifiedModuleLoaderClient\Cli\Command;

use RobinTheHood\ModifiedModuleLoaderClient\Cli\MmlcCli;

class CommandCreate
{
    public function __construct()
    {
    }

    public function run(MmlcCli $cli): void
    {
        if ($cli->hasOption('-i') || $cli->hasOption('--interactive')) {
            $this->createInteractive($cli);
        } else {
            $this->create($cli);
        }

        var_dump($cli->getArgument(0));
        var_dump($cli->getArgument(1));
        var_dump($cli->getArgument(2));
        var_dump($cli->getArgument(3));
    }

    private function create(MmlcCli $cli): void
    {
        echo "🏗️ \n";
    }

    private function createInteractive(MmlcCli $cli): void
    {
        echo "👾 \n";
        echo "Type you module name: \n";
        $input = '';
        while (!$input) {
            $input = readline();
            echo "Try again: ";
        }
        echo \PHP_EOL;
        echo "Your module name is $input\n";
    }

    public function help()
    {
        $this->renderHeading('Description:');
        echo "  Creates a new module. Can be done interactively.\n";
        echo "\n";
        $this->renderHeading('Usage:');
        echo "  create [options] <archiveName> \n";
        echo "\n";
        $this->renderHeading('Arguments:');
        $this->renderOption('archiveName', 'The name of the archive (vendorName/moduleName).');
        echo "\n";
        $this->renderHeading('Options:');
        $this->renderOption('--prefix=VENDOR_PREFIX', 'Usually an abbreviated vendorName. Can also be vendorName.');
        $this->renderOption('-i, --interactive', 'Whether to create the module interactively (by answering questions).');
    }


    private function renderHeading(string $heading): void
    {
        echo "\e[33m$heading\e[0m\n";
    }

    private function renderOption($name, $description)
    {
        $name = $this->rightPad($name, 30);
        echo "  \e[32m$name\e[0m $description\n";
    }

    private function rightPad($text, $totalLength)
    {
        $textLength = strlen($text);

        if ($textLength >= $totalLength) {
            return $text; // Der Text ist bereits länger oder gleich der Ziel-Länge
        }

        $paddingLength = $totalLength - $textLength;
        $padding = str_repeat(' ', $paddingLength);

        return $text . $padding;
    }
}
