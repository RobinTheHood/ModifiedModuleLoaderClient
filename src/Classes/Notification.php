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

    public static function renderFlashMessages(): string
    {
        $flashMessages = self::pullAllFlashMessages();
        $html = '';
        foreach ($flashMessages as $flashMessage) {
            $html .= self::renderFlashMessage($flashMessage['text'], $flashMessage['type']) . "\n";
        }
        return $html;
    }

    public static function renderFlashMessage(string $message, string $type): string
    {
        if ($type == 'warning') {
            return '<div class="alert alert-warning" role="alert"><i class="fas fa-exclamation-triangle fa-fw"></i> ' . $message . '</div>';
        } elseif ($type == 'error') {
            return '<div class="alert alert-danger" role="alert">' . $message . '</div>';
        } elseif ($type == 'success') {
            return '<div class="alert alert-success auto-fade-out" role="alert"><i class="fas fa-check fa-fw"></i> ' . $message . '</div>';
        }
        return '<div class="alert alert-info" role="alert">' . $message . '</div>';
    }

    /**
     * @param array<string, string> $message
     */
    public static function pushFlashMessage(array $message): void
    {
        if (isset($_SESSION['mml']) && isset($_SESSION['mml']['flashMessages'])) {
            foreach ($_SESSION['mml']['flashMessages'] as $oldMessage) {
                if ($oldMessage['text'] == $message['text']) {
                    return;
                }
            }
        }

        $_SESSION['mml']['flashMessages'][] = $message;
    }

    public static function pullAllFlashMessages(): array
    {
        $flashMessages = isset($_SESSION['mml']['flashMessages']) ? $_SESSION['mml']['flashMessages'] : [];
        $_SESSION['mml']['flashMessages'] = [];
        return $flashMessages;
    }
}
