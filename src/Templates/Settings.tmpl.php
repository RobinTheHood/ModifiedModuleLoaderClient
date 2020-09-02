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

            <div class="row">
                <div class="col-3">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active" id="v-pills-general-tab" data-toggle="pill" href="#v-pills-general" role="tab" aria-controls="v-pills-general" aria-selected="true">Allgemein</a>
                        <a class="nav-link" id="v-pills-user-tab" data-toggle="pill" href="#v-pills-user" role="tab" aria-controls="v-pills-user" aria-selected="true">Benutzer</a>
                        <a class="nav-link" id="v-pills-advanced-tab" data-toggle="pill" href="#v-pills-advanced" role="tab" aria-controls="v-pills-advanced" aria-selected="true">Erweitert</a>
                    </div>
                </div>
                <div class="col-9">
                    <div class="tab-content" id="v-pills-tabContent">
                        <!-- General -->
                        <div class="tab-pane fade show active" id="v-pills-general" role="tabpanel" aria-labelledby="v-pills-general-tab">
                            <form method="post">
                                <div class="form-group row">
                                    <label for="inputAccessToken" class="col-sm-2 col-form-label">Access Token</label>
                                    <div class="col-sm-10">
                                        <input type="text" name="accessToken" class="form-control" id="inputAccessToken" value="<?php echo Config::getAccessToken(); ?>">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-10">
                                        <button type="submit" class="btn btn-primary">Speichern</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- User -->
                        <div class="tab-pane fade" id="v-pills-user" role="tabpanel" aria-labelledby="v-pills-user-tab">
                            <form method="post">
                                <div class="form-group row">
                                    <label for="inputUsername" class="col-sm-2 col-form-label">Benutzername</label>
                                    <div class="col-sm-10">
                                        <input type="text" name="username" class="form-control" id="inputUsername" value="<?php echo Config::getUsername(); ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputPassword" class="col-sm-2 col-form-label">Password</label>
                                    <div class="col-sm-10">
                                        <input type="password" name="password" class="form-control" id="inputPassword">
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

                        <!-- Advanced -->
                        <div class="tab-pane fade show" id="v-pills-advanced" role="tabpanel" aria-labelledby="v-pills-advanced-tab">
                            <form method="post">
                                <div class="form-group row">
                                    <label for="inputModulesLocalDir" class="col-sm-2 col-form-label">Module Pfad</label>
                                    <div class="col-sm-10">
                                        <input type="text" name="modulesLocalDir" class="form-control" id="inputModulesLocalDir" value="<?php echo Config::getModulesLocalDir(); ?>">
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
