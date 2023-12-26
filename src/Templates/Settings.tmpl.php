<?php

/**
 * @phpcs:disable PSR1.Files.SideEffects
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */

defined('LOADED_FROM_INDEX') && LOADED_FROM_INDEX ?? die('Access denied.');

use RobinTheHood\ModifiedModuleLoaderClient\Config;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;
use RobinTheHood\ModifiedModuleLoaderClient\ViewModels\NotificationViewModel;

$notificationView = new NotificationViewModel();

function viewIsSelected(bool $value): string
{
    if (!$value) {
        return '';
    }
    return 'selected="selected"';
}
?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <?php include 'Head.tmpl.php' ?>
    </head>

    <body>
        <?php include 'Navi.tmpl.php' ?>

        <div class="content">
            <?= $notificationView->renderFlashMessages() ?>

            <h1>Einstellungen</h1>

            <p>Mehr Information zu den Einstellungen kannst du dir unter <a target="_blank" href="https://module-loader.de/docs/config_config.php">module-loader.de</a> durchlesen.</p>
            <br>

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
                            <h2>Allgemein</h2>
                            <form action="?action=settings&section=general" method="post">

                                <!-- accessToken -->
                                <div class="form-group">
                                    <label for="inputAccessToken">AccessToken</label>
                                    <div class="input-group mb-3">
                                        <input type="text" name="accessToken" class="form-control" id="inputAccessToken" value="<?= Config::getAccessToken(); ?>"<?= empty(Config::getAccessToken()) ? '' : 'readonly'; ?>>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" id="button-addon2" onclick="copyToClipboard('inputAccessToken')">kopieren</button>
                                        </div>
                                    </div>
                                    <p>Aus Sicherheitsgründen ist das Ändern des AccessTokens gesperrt. Der Wert kann unter <code style="word-break: break-all"><?= Config::path(); ?></code> geändert werden.</p>
                                </div>

                                <!-- adminDir -->
                                <div class="form-group">
                                    <label for="inputAdminDir">Admin-Verzeichnis</label>
                                    <input type="text" name="adminDir" class="form-control" id="inputAdminDir" value="<?= Config::getAdminDir(); ?>">
                                    <p>Der MMLC kann dein Admin-Verzeichnis automatischen finden, auch wenn es umbenannt wurde. Sollte das nicht funktionieren, kann hier der Namen des Admin-Verzeichnis eintragen werden. Lasse das Feld leer, wenn der MMLC automatisch veruschen soll, das Admin-Verzeichnis zu finden. Standard-Wert ist kein Wert oder <code>admin</code></p>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Speichern</button>
                                </div>
                            </form>
                        </div>

                        <!-- User -->
                        <div class="tab-pane fade" id="v-pills-user" role="tabpanel" aria-labelledby="v-pills-user-tab">
                            <h2>Benutzer</h2>
                            <form action="?action=settings&section=user" method="post">
                                <div class="form-group">
                                    <label for="inputUsername">Benutzername</label>
                                    <input type="text" name="username" class="form-control" id="inputUsername" value="<?= Config::getUsername(); ?>">
                                    <p>Mit diesem Namen meldest du dich im MMLC an.</p>
                                </div>

                                <div class="form-group">
                                    <label for="inputPassword">Password</label>
                                    <input type="password" name="password" class="form-control" id="inputPassword">
                                    <p>Gib ein neues Passwort ein, wenn du es ändern möchtest.</p>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Speichern</button>
                                </div>
                            </form>
                        </div>

                        <!-- Advanced -->
                        <div class="tab-pane fade show" id="v-pills-advanced" role="tabpanel" aria-labelledby="v-pills-advanced-tab">
                            <h2>Erweitert</h2>
                            <form action="?action=settings&section=advanced" method="post">
                                <!-- shopRoot -->
                                <div class="form-group">
                                    <label for="inputShopRoot">Shop Root</label>
                                    <input type="text" name="shopRoot" class="form-control" id="inputShopRoot" value="<?= Config::getShopRoot(); ?>">
                                    <p>Verzeichnis vom modified-shop. Lasse dieses Feld leer für die Standard Einstellung.</p>
                                </div>

                                <!-- modulesLocalDir -->
                                <div class="form-group">
                                    <label for="inputModulesLocalDir">Module Pfad</label>
                                    <input type="text" name="modulesLocalDir" class="form-control" id="inputModulesLocalDir" value="<?= Config::getModulesLocalDir(); ?>">
                                    <p>In diesem Ordner werden Module für den MMLC heruntergeladen. Der Standard Wert ist <code>Modules</code></p>
                                </div>

                                <!-- logging -->
                                <div class="form-group">
                                    <label for="inputLogging">Logging</label>
                                    <select name="logging" class="form-control" id="inputLogging" size="1">
                                        <option <?= viewIsSelected(Config::getLogging() === true) ?> value="true">true</option>
                                        <option <?= viewIsSelected(Config::getLogging() === false) ?> value="false">false</option>
                                    </select>

                                    <p>Sollen (Fehler-) Meldungen im Verzeichnis <code>ModifiedModuleLoaderClient/logs</code> protokolliert werden?</p>
                                </div>

                                <!-- dependencyMode -->
                                <div class="form-group">
                                    <label for="inputDependencyMode">Abhängigkeitsmodus</label>
                                    <select name="dependencyMode" class="form-control" id="inputDependencyMode" size="1">
                                        <option <?= viewIsSelected(Config::getDependenyMode() === Comparator::CARET_MODE_STRICT) ?> value="strict">strict</option>
                                        <option <?= viewIsSelected(Config::getDependenyMode() === Comparator::CARET_MODE_LAX) ?> value="lax">lax</option>
                                    </select>

                                    <p>Du kannst zwischen <code>strict</code> und <code>lax</code> wählen. Mit <code>strict</code> werden die Abhängigkeiten von Modulen mit einer Version kleiner als 1.0.0 genauer kontrolliert. Wenn sich einige Module nicht installieren lassen, kannst du es mit <code>lax</code> versuchen. Beachte, dass im Lex-Modus die Wahrscheinlichkeit größer ist, dass verschiedene Module nicht miteinander harmonieren.</p>
                                </div>

                                <!-- installMode -->
                                <div class="form-group">
                                    <label for="inputInstallMode">Installationsmodus</label>
                                    <select name="installMode" class="form-control" id="inputInstallMode" size="1">
                                        <option <?= viewIsSelected(Config::getInstallMode() == 'copy') ?> value="copy">copy</option>
                                        <option <?= viewIsSelected(Config::getInstallMode() == 'link') ?> value="link">link</option>
                                    </select>

                                    <p>Du kannst zwischen <code>copy</code> und <code>link</code> wählen. Hast du den MMLC in einem Live-Shop im Einsatz, wähle <code>copy</code>. Wenn du mit dem MMLC Module entwickelst, wähle <code>link</code>.</p>
                                </div>

                                <!-- exceptionMonitorDomain -->
                                <div class="form-group">
                                    <label for="inputExceptionMonitorDomain">ExceptionMonitor Domain</label>
                                    <input type="text" name="exceptionMonitorDomain" class="form-control" id="inputExceptionMonitorDomain" value="<?= Config::getExceptionMonitorDomain(); ?>">
                                    <p>Wenn der MMLC programmierfehler im Browser anzeigen soll, kannst hier die Domain eintragen werden, für der ExcpetionMontir fehler anzeigen soll. Der ExceptionMonitor wird bei Fehlern aktiv, sobald die hinterlegte Domain die Gleiche ist, von der der MMLC aufgerufen wird. Beispiel <code>www.example.org</code></p>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Speichern</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            var urlParams = new URLSearchParams(window.location.search);
            var section = urlParams.get('section');
            var tabId = '#v-pills-' + section + '-tab'
            $(function () {
                $(tabId).tab('show')
            })
        </script>

        <?php include 'Footer.tmpl.php' ?>
    </body>
</html>
