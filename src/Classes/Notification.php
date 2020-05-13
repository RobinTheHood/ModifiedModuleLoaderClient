<?php

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

    public static function renderFlashMessages()
    {
        $flashMessages = self::pullAllFlashMessages();
        $html = '';
        foreach ($flashMessages as $flashMessage) {
            $html .=self::renderFlashMessage($flashMessage['text'], $flashMessage['type']) . "\n";
        }
        return $html;
    }

    public static function renderFlashMessage($message, $type)
    {
        if ($type == 'warning') {
            return '<div class="alert alert-warning" role="alert">' . $message . '</div>';
        } elseif ($type == 'error') {
            return '<div class="alert alert-danger" role="alert">' . $message . '</div>';
        }
        return '<div class="alert alert-info" role="alert">' . $message . '</div>';
    }

    public static function pushFlashMessage($message)
    {
        foreach ($_SESSION['mml']['flashMessages'] as $oldMessage) {
            if ($oldMessage['text'] == $message['text']) {
                return;
            }
        }

        $_SESSION['mml']['flashMessages'][] = $message;
    }

    public static function pullAllFlashMessages()
    {
        $flashMessages = isset($_SESSION['mml']['flashMessages']) ? $_SESSION['mml']['flashMessages'] : [];
        $_SESSION['mml']['flashMessages'] = [];
        return $flashMessages;
    }
}
