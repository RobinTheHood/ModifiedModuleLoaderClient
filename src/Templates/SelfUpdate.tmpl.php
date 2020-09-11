<?php if (!defined('LOADED_FROM_INDEX') || LOADED_FROM_INDEX != 'true') { die('Access denied.'); }?>
<?php use RobinTheHood\ModifiedModuleLoaderClient\Config; ?>

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
                <?php echo $installedVersion ?><br><br>

                <?php if ($comparator->greaterThan($version['version'], $installedVersion)) { ?>
                    Version <?php echo $version['version'] ?> verfügbar<br><br>
                    <a class="button button-success" href="<?php echo App::getUrlRoot();?>?action=selfUpdate&install=<?php echo $version['version']?>">Update installieren</a>
                <?php } else { ?>
                    Diese Version ist aktuell.
                <?php } ?>

                <br><br>
                <small>
                    <?php if (Config::getAccessToken()) { ?>
                        AccessToken: <?php echo Config::getAccessToken(); ?>
                    <?php } else {?>
                        AccessToken eintragen unter:<br>
                        <i>/ModifiedModuleLoaderClient/config/config.php</i><br>
                    <?php } ?>
                    <br>
                    Domain: <?php echo $_SERVER['SERVER_NAME']; ?>
                </small>
            </div>
        </div>
    </body>
</html>
