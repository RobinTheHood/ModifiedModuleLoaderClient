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
use RobinTheHood\ModifiedModuleLoaderClient\Notification;
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\ArrayHelper;

class SendMail
{
  public function sendFeedback() 
  {
    $fromEmail = ArrayHelper::getIfSet($_POST, 'email', '');
    $from = ArrayHelper::getIfSet($_POST, 'name', '');
    $message = ArrayHelper::getIfSet($_POST, 'message', '');
    if ($fromEmail == '' || $from == '' || $message == '') {
      Notification::pushFlashMessage(
        array(
          "text" => "Warnung: Felder k&ouml;nnen nicht leer gelassen werden.", 
          "type" => "error"
        )
      );
      return;
    }
<<<<<<< HEAD
    $to      = 'info@module-loader.de';
=======
    $to      = 'd.bjelajac@hoqmee.net';
>>>>>>> 6f0fd2da1a35f193cb77eb4d64af6ebf91575596
    $subject = 'Report problem';
    $shopVersion = ShopInfo::getModifiedVersion();
    $message .= "<br />Modified version: " . $shopVersion . '<br />';
    $message .= 'Browser: ' . $_SERVER['HTTP_USER_AGENT'] . '<br />';
    $message .= 'PHP version: ' . phpversion() . '<br />';
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=iso-8859-1';
    $headers[] = 'From: '.$from.' <'.$fromEmail.'>';
    if (mail($to, $subject, $message, implode("\r\n", $headers))) {
      Notification::pushFlashMessage(
        array(
          "text" => "Erfolg: Die Nachricht wurde erfolgreich gesendet, wir werden so schnell wie m&ouml;glich antworten.", 
          "type" => "info"
        )
      );
    }
  }
}