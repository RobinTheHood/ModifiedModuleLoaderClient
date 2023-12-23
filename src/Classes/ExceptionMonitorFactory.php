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

namespace RobinTheHood\ModifiedModuleLoaderClient;

use RobinTheHood\ExceptionMonitor\ExceptionMonitorObj;
use RobinTheHood\ExceptionMonitor\Handler\BrowserHandler;
use RobinTheHood\ExceptionMonitor\Handler\CallbackHandler;
use RobinTheHood\ModifiedModuleLoaderClient\Logger\LogLevel;
use RobinTheHood\ModifiedModuleLoaderClient\Logger\StaticLogger;
use Throwable;

class ExceptionMonitorFactory
{
    public const DEFAULT_DOMAIN = 'mmlc.ddev.site';

    public static function getExceptionMonitor(): ExceptionMonitorObj
    {
        $loggerHandler = new CallbackHandler(function (Throwable $exception) {
            StaticLogger::log(
                LogLevel::ERROR,
                $exception->getMessage()
                . "\n" . $exception->getFile() . ':' . $exception->getLine() . "\n" . print_r($_SERVER, true)
            );
        });

        $publicMessageHandler = new CallbackHandler(function (Throwable $exception) {
            if (ob_get_length() > 0 || ob_get_level() > 0) {
                ob_end_clean();
            }
            header("HTTP/1.0 500 Internal Server Error");
            echo self::createMessage();
            die();
        });

        $domain = Config::getExceptionMonitorDomain() ?? self::DEFAULT_DOMAIN;
        $exceptionMonitor = new ExceptionMonitorObj();

        $exceptionMonitor->addHandler($loggerHandler);
        if (($_SERVER['SERVER_NAME'] ?? '') === $domain) {
            $exceptionMonitor->addHandler(new BrowserHandler());
        } else {
            $exceptionMonitor->addHandler($publicMessageHandler);
        }

        return $exceptionMonitor;
    }

    private static function createMessage(): string
    {
        $domainString = $_SERVER['SERVER_NAME'] ? '(' . $_SERVER['SERVER_NAME'] . ')' : ' ';

        $errorMessage = ""
                    . "<h1>❌ An error has occurred</h1>\n"
                    . "No reason to panic.\n"
                    . "<h3>🛠️ What can you do:</h3>\n"
                    . "<ul>\n"
                    . "<li>Variant A: Activate the logs by entering the following in the config:<br>"
                    . "<code>'logging' => 'true'</code> "
                    . "and check the logs in <code>SHOP-ROOT/ModifiedModuleLoaderClient/logs/</code></li>\n"
                    . "<li>Variant B: Activate the ExceptionMonitor to display the error in your browser by "
                    . "entering your domain $domainString in the config:<br>"
                    . "<code>'exceptionMonitorDomain' => 'www.your-domain.org'</code></li>\n"
                    . "</ul>\n"
                    . "<h3>📖 Documentation</h3>\n"
                    . "Follow the link to view the documentation for the config.php file in a new window: "
                    . "<a target=\"_blank\" "
                    . "href=\"https://module-loader.de/docs/config_config.php\">module-loader.de/docs</a>";

        $css = "
            <style>
                .message-frame {
                    max-width: 800px;
                    margin: 50px auto;
                    padding: 40px;
                    font-family: Arial;
                    border-radius: 5px;
                    box-shadow: 0 0 12px 0 rgba(0, 0, 0, 0.25);
                    line-height: 24px;
                    font-size: 16px;
                }

                .message-frame li {
                    margin-bottom: 20px
                }
            </style>
        ";

        $errorMessage = ''
            . $css
            . "\n"
            . '<div class="message-frame">'
                . $errorMessage
            . '</div>';

        return $errorMessage;
    }
}
