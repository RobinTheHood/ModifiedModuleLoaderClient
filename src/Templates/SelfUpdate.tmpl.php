<?php
defined('LOADED_FROM_INDEX') && LOADED_FROM_INDEX ?? die('Access denied.');

use RobinTheHood\ModifiedModuleLoaderClient\Config;
use RobinTheHood\ModifiedModuleLoaderClient\ViewModels\NotificationViewModel;

$notificationView = new NotificationViewModel();
?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <?php include 'Head.tmpl.php' ?>
    </head>

    <body>
        <?php include 'Navi.tmpl.php' ?>

        <div class="content" style="text-align: center">
            <?= $notificationView->renderFlashMessages() ?>

            <div class="self-update">
                <h2>MMLC - Modified Module Loader Client</h2>
                <?= $installedVersionString ?><br><br>

                <?php if ($mmlcVersionInfo) { ?>
                    Version <?= $mmlcVersionInfo->version ?> verfügbar<br><br>
                    <a class="button button-success" href="?action=selfUpdate&install=<?= $mmlcVersionInfo->version ?>">
                        Update installieren
                    </a>
                <?php } else { ?>
                    Diese Version ist aktuell.
                <?php } ?>

                <br><br>
                <small>
                    <?php if (Config::getAccessToken()) { ?>
                        AccessToken: <?= Config::getAccessToken(); ?>
                    <?php } else {?>
                        AccessToken eintragen unter:<br>
                        <i>/ModifiedModuleLoaderClient/config/config.php</i><br>
                    <?php } ?>
                    <br>
                    Domain: <?= $serverName ?>
                </small>
            </div>
        </div>
    </body>
</html>