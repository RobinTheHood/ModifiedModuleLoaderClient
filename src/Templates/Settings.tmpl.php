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
            if (!empty($_POST['username'])) {
                Config::setUsername($_POST['username']);
            }

            if (!empty($_POST['password'])) {
                Config::setPassword($_POST['password']);
            }
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
                            <form method="post">
                                <div class="form-group row">
                                    <label for="inputEmail3" class="col-sm-2 col-form-label">Benutzername</label>
                                    <div class="col-sm-10">
                                        <input type="text" name="username" class="form-control" id="inputEmail3" value="<?php echo Config::getUsername(); ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputPassword3" class="col-sm-2 col-form-label">Password</label>
                                    <div class="col-sm-10">
                                        <input type="password" name="password" class="form-control" id="inputPassword3">
                                        <p>Gib ein neues Passwort ein, wenn du es ändern möchtest.</p>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-10">
                                        <button type="submit" class="btn btn-primary">Speichern</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'Footer.tmpl.php' ?>
    </body>
</html>
