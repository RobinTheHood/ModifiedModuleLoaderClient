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

namespace RobinTheHood\ModifiedModuleLoaderClient\ViewModels;

use RobinTheHood\ModifiedModuleLoaderClient\Notification;

class NotificationViewModel
{
    public function renderFlashMessages(): string
    {
        $flashMessages = Notification::pullAllFlashMessages();
        $html = '';
        foreach ($flashMessages as $flashMessage) {
            $html .= self::renderFlashMessage($flashMessage['text'], $flashMessage['type']) . "\n";
        }
        return $html;
    }

    public function renderFlashMessage(string $message, string $type): string
    {
        if ($type == 'warning') {
            return '<div class="alert alert-warning" role="alert"><i class="fas fa-exclamation-triangle fa-fw"></i> ' . $message . '</div>';
        } elseif ($type == 'error') {
            return '<div class="alert alert-danger" role="alert"> <i class="fas fa-ban fa-fw"></i>' . $message . '</div>';
        } elseif ($type == 'success') {
            return '<div class="alert alert-success auto-fade-out" role="alert"><i class="fas fa-check fa-fw"></i> ' . $message . '</div>';
        }
        return '<div class="alert alert-info" role="alert">' . $message . '</div>';
    }
}
