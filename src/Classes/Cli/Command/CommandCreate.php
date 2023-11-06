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
use RobinTheHood\ModifiedModuleLoaderClient\Cli\TextRenderer;

class CommandCreate implements CommandInterface
{
    private const ARGUMENT_ARCHIVE_NAME = 0;

    public function __construct()
    {
    }

    public function getName(): string
    {
        return 'create';
    }

    public function run(MmlcCli $cli): void
    {
        if ($cli->hasOption('-i') || $cli->hasOption('--interactive')) {
            $this->createInteractive($cli);
        } else {
            $this->create($cli);
        }
    }

    private function create(MmlcCli $cli): void
    {
        echo "🏗️ \n";
    }

    private function createInteractive(MmlcCli $cli): void
    {
        $archiveName = $cli->getFilteredArgument(self::ARGUMENT_ARCHIVE_NAME);

        if (!$archiveName) {
            $vendorName = '';

            while (!$vendorName) {
                echo "1. What is the vendor name?\n";
                echo "   Vendor name: ";

                $vendorName = readline();

                echo "\n";
            }

            $moduleName = '';

            while (!$moduleName) {
                echo "2. What is the module name?\n";
                echo "   Module name: ";

                $moduleName = readline();

                echo "\n";
            }

            $archiveName = $vendorName . '/' . $moduleName;
        }

        $this->create($cli);
    }

    public function runHelp(MmlcCli $cli): void
    {
        $padding = 27;

        echo "\n";
        TextRenderer::renderHelpHeading('Description:');
        echo "  Creates a new module. Can be done interactively.\n";
        echo "\n";

        TextRenderer::renderHelpArgument('Usage:', 'create [options] <archiveName>', $padding);
        echo "\n";

        TextRenderer::renderHelpHeading('Arguments:');
        TextRenderer::renderHelpOption('', 'archiveName', 'The name of the archive (vendorName/moduleName).', $padding);
        echo "\n";

        TextRenderer::renderHelpHeading('Options:');
        TextRenderer::renderHelpOption('', 'prefix=VENDOR_PREFIX', 'Usually an abbreviated vendorName. Can also be vendorName.', $padding);
        TextRenderer::renderHelpOption('i', 'interactive', 'Whether to create the module interactively (by answering questions).', $padding);
        echo "\n";

        echo "Read more at https://module-loader.de/documentation.php\n";
    }
}
