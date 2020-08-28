<?php if (!defined('LOADED_FROM_INDEX') || LOADED_FROM_INDEX != 'true') { die('Access denied.'); }?>
<?php use RobinTheHood\ModifiedModuleLoaderClient\Config; ?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <?php include 'Head.tmpl.php' ?>
    </head>

    <body>
        <?php include 'Navi.tmpl.php' ?>

        <div class="content">
            <h1>Einstellungen</h1>
            <form method="post">
                <table>
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        Config::setOptions($_POST);
                    }
                    ?>
                    <?php foreach (Config::getOptions() as $key => $value) { ?>
                    <tr>
                        <td><?php echo $key; ?></td>
                        <td><input type="text" name="<?php echo $key; ?>" value="<?php echo $value; ?>"></td>
                    </tr>
                    <?php } ?>
                </table>

                <button type="submit">Speichern</button>
            </form>
        </div>

        <?php include 'Footer.tmpl.php' ?>
    </body>
</html>
