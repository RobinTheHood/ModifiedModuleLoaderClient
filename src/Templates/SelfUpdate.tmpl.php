<?php if (!defined('LOADED_FROM_INDEX') || LOADED_FROM_INDEX != 'true') { die('Access denied.'); }?>
<?php global $configuration; ?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <?php include 'Head.tmpl.php' ?>
    </head>

    <body>
        <?php include 'Navi.tmpl.php' ?>

        <style>
            .self-update {
                text-align: center;
            }
        </style>

        <div class="content">
            <div class="self-update">
                <h2>MMLC - Modified Module Loader Client</h2>
                <?php echo $installedVersion ?><br><br>

                <?php if ($semver->greaterThan($version['version'], $installedVersion)) { ?>
                    Version <?php echo $version['version'] ?> verfügbar<br><br>
                    <a class="button button-success" href="?action=selfUpdate&install=<?php echo $version['version']?>">Update installieren</a>
                <?php } else { ?>
                    Diese Version ist aktuell.
                <?php } ?>

                <br><br>
                <small>
                    <?php if ($configuration['accessToken']) { ?>
                        AccessToken: <?php echo $configuration['accessToken']; ?>
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
