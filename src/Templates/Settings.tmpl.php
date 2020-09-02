<?php if (!defined('LOADED_FROM_INDEX') || LOADED_FROM_INDEX != 'true') { die('Access denied.'); }?>
<?php use RobinTheHood\ModifiedModuleLoaderClient\Config; ?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <?php include 'Head.tmpl.php' ?>
    </head>

    <body>
        <?php include 'Navi.tmpl.php' ?>

        <?php
        /**
         * Save submitted form input to config.
         */
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Config::setOptions($_POST);
        }
        ?>

        <div class="content">
            <h1>Einstellungen</h1>

            <div class="row">
                <div class="col-3">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active" id="v-pills-user-tab" data-toggle="pill" href="#v-pills-user" role="tab" aria-controls="v-pills-user" aria-selected="true">
                            Benutzer
                        </a>
                    </div>
                </div>
                <div class="col-9">
                    <div class="tab-content" id="v-pills-tabContent">
                        <div class="tab-pane fade show active" id="v-pills-user" role="tabpanel" aria-labelledby="v-pills-user-tab">
                            <h2>Benutzer</h2>
                            <form method="post">
                                <table>
                                    <?php foreach (Config::getOptionsUser(['pretty']) as $key => $value) { ?>
                                    <tr>
                                        <td><?php echo $key; ?></td>
                                        <td><input type="text" name="<?php echo $key; ?>" value="<?php echo $value; ?>"></td>
                                    </tr>
                                    <?php } ?>
                                </table>

                                <button type="submit">Speichern</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'Footer.tmpl.php' ?>
    </body>
</html>
