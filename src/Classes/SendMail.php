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

use RobinTheHood\ModifiedModuleLoaderClient\ShopInfo;
use RobinTheHood\ModifiedModuleLoaderClient\SelfUpdater;
use RobinTheHood\ModifiedModuleLoaderClient\Notification;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\ArrayHelper;

class SendMail
{
    public static function sendIssue() 
    {
        $fromEmail = ArrayHelper::getIfSet($_POST, 'email', '');
        $from = ArrayHelper::getIfSet($_POST, 'name', '');
        $message = ArrayHelper::getIfSet($_POST, 'message', '');
        
        if ($fromEmail == '' || $from == '' || $message == '') {
            Notification::pushFlashMessage(
                [
                  'text' => 'Warnung: Felder können nicht leer gelassen werden.', 
                  'type' => 'error'
                ]
            );
            return;
        }

        $to = 'info@module-loader.de';
        $subject = 'MMLC Report Issue';
        $shopVersion = ShopInfo::getModifiedVersion();
        
        $selfUpdater = new SelfUpdater();
        $mmlcVersion = $selfUpdater->getInstalledVersion();

        $message .=
            '<hr />Message sent from: ' . $_SERVER['HTTP_HOST'] .
            '<br />Modified version: ' . $shopVersion . 
            '<br />MMLC version: ' . $mmlcVersion . 
            '<br />Browser: ' . $_SERVER['HTTP_USER_AGENT'] .
            '<br />PHP version: ' . phpversion();
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . $from . ' <' . $fromEmail . '>'
        ];

        if (mail($to, $subject, $message, implode("\r\n", $headers))) {
            Notification::pushFlashMessage(
                [
                  'text' => 'Erfolg: Die Nachricht wurde erfolgreich gesendet, wir werden so schnell wie möglich antworten.', 
                  'type' => 'info'
                ]
            );
        }
    }
}