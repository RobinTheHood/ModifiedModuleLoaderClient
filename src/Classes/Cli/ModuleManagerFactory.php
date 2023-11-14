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

use RobinTheHood\ModifiedModuleLoaderClient\Cli\MmlcCli;
use RobinTheHood\ModifiedModuleLoaderClient\Cli\TextRenderer;
use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleManager\ModuleManager;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleManager\ModuleManagerLog;

class ModuleManagerFactory
{
    public static function create(MmlcCli $cli)
    {
        $moduleManagerLog = new ModuleManagerLog();
        $moduleManagerLog->setWriteFunction(
            function (string $message, $data1, $data2) use ($cli) {
                $cli->writeLine(self::formatMessage($message, $data1, $data2));
            }
        );

        $moduleManagerLog->setErrorFunction(
            function (int $errorNo, string $message, $data1, $data2) use ($cli) {
                $cli->writeLine(
                    TextRenderer::color("Error $errorNo: ", TextRenderer::COLOR_RED)
                    . self::formatMessage($message, $data1, $data2)
                );
            }
        );

        $moduleManager = ModuleManager::createFromConfig();
        $moduleManager->setLog($moduleManagerLog);
        return $moduleManager;
    }

    private static function formatMessage(string $message, $data1, $data2): string
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
