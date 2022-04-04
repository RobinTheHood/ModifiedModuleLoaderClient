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

class Notification
{
    private const MMLC_SESSION_NAME = 'mmlc';

    /**
     * @param array<string, string> $message
     */
    public static function pushFlashMessage(array $message): void
    {
        if (isset($_SESSION[self::MMLC_SESSION_NAME]) && isset($_SESSION[self::MMLC_SESSION_NAME]['flashMessages'])) {
            foreach ($_SESSION[self::MMLC_SESSION_NAME]['flashMessages'] as $oldMessage) {
                if ($oldMessage['text'] == $message['text']) {
                    return;
                }
            }
        }

        $_SESSION[self::MMLC_SESSION_NAME]['flashMessages'][] = $message;
    }

    public static function pullAllFlashMessages(): array
    {
        $flashMessages = isset($_SESSION[self::MMLC_SESSION_NAME]['flashMessages']) ? $_SESSION[self::MMLC_SESSION_NAME]['flashMessages'] : [];
        $_SESSION[self::MMLC_SESSION_NAME]['flashMessages'] = [];
        return $flashMessages;
    }
}
