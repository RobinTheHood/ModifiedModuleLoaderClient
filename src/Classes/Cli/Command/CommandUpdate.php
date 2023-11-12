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
use RobinTheHood\ModifiedModuleLoaderClient\DependencyManager\DependencyException;
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleManager\ModuleManager;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleManager\ModuleManagerLog;
use RuntimeException;

class CommandUpdate implements CommandInterface
{
    public function __construct()
    {
    }

    public function getName(): string
    {
        return 'update';
    }

    public function run(MmlcCli $cli): void
    {
        $archiveName = $cli->getFilteredArgument(0);

        if (!$archiveName) {
            $cli->writeLine($this->getHelp($cli));
            return;
        }

        try {
            $moduleManager = $this->createModuleManager($cli);
            $newModule = $moduleManager->update($archiveName);
        } catch (RuntimeException $e) {
            $cli->writeLine(TextRenderer::color('Exception:', TextRenderer::COLOR_RED) . ' ' . $e->getMessage());
            die();
        } catch (DependencyException $e) {
            $cli->writeLine(TextRenderer::color('Exception:', TextRenderer::COLOR_RED) . ' ' . $e->getMessage());
            die();
        }

        $cli->writeLine(TextRenderer::color('ready', TextRenderer::COLOR_GREEN));
        return;
    }

    public function getHelp(MmlcCli $cli): string
    {
        return
            TextRenderer::renderHelpHeading('Description:')
            . "  Updates a isntalled module.\n"
            . "\n"

            . TextRenderer::renderHelpHeading('Usage:')
            . "  update <archiveName>\n"
            . "\n"

            . TextRenderer::renderHelpHeading('Arguments:')
            . TextRenderer::renderHelpArgument('archiveName', 'The archiveName of the module to be updated.')
            . "\n"

            . TextRenderer::renderHelpHeading('Options:')
            . TextRenderer::renderHelpOption('h', 'help', 'Display help for the given command.')
            . "\n"

            . "Read more at https://module-loader.de/documentation.php";
    }

    private function createModuleManager(MmlcCli $cli)
    {
        $moduleManagerLog = new ModuleManagerLog();
        $moduleManagerLog->setWriteFunction(
            function (string $message, $data1, $data2) use ($cli) {
                $cli->writeLine($this->formatMessage($message, $data1, $data2));
            }
        );

        $moduleManagerLog->setErrorFunction(
            function (int $errorNo, string $message, $data1, $data2) use ($cli) {
                $cli->writeLine(
                    TextRenderer::color("Error $errorNo: ", TextRenderer::COLOR_RED)
                    . $this->formatMessage($message, $data1, $data2)
                );
            }
        );

        $moduleManager = ModuleManager::createFromConfig();
        $moduleManager->setLog($moduleManagerLog);
        return $moduleManager;
    }

    private function formatMessage(string $message, $data1, $data2): string
    {
        $value = '';
        if (is_string($data1) && is_string($data2)) {
            $value =
                "module " . TextRenderer::color($data1, TextRenderer::COLOR_GREEN)
                . " version " . TextRenderer::color($data2, TextRenderer::COLOR_YELLOW);
        } elseif (is_string($data1)) {
            $value = TextRenderer::color($data1, TextRenderer::COLOR_GREEN);
        } elseif ($data1 instanceof Module) {
            /** @var Module */
            $module = $data1;
            $value =
                "module " . TextRenderer::color($module->getArchiveName(), TextRenderer::COLOR_GREEN)
                . " version " . TextRenderer::color($module->getVersion(), TextRenderer::COLOR_YELLOW);
        }
        $formatedMessage = sprintf($message, $value);
        return $formatedMessage;
    }
}
