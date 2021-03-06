<?php
defined('LOADED_FROM_INDEX') && LOADED_FROM_INDEX ?? die('Access denied.');

use RobinTheHood\ModifiedModuleLoaderClient\Config;

?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <?php include 'Head.tmpl.php' ?>
    </head>

    <body>
        <?php include 'Navi.tmpl.php' ?>

        <div class="content" style="text-align: center">
            <div class="self-update">
                <h2>MMLC - Modified Module Loader Client</h2>
                <?= $installedVersion ?><br><br>

                <?php if ($comparator->greaterThan($version['version'], $installedVersion)) { ?>
                    Version <?= $version['version'] ?> verfügbar<br><br>
                    <a class="button button-success" href="?action=selfUpdate&install=<?= $version['version'] ?>">
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