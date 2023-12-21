<?php

/**
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */

defined('LOADED_FROM_INDEX') && LOADED_FROM_INDEX ?? die('Access denied.');

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
        <div class="content">
            <div>
                <?= $notificationView->renderFlashMessages() ?>
                <div class="row">
                    <div class="offset-3 col-6">
                        <h2>Nachricht an MMLC-Entwickler senden</h2>
                        
                        <br>
                        <p>Wenn du einen Fehler im MMLC gefunden oder eine Frage hast, kannst du uns mit diesem Formular eine E-Mail an <a href="mailto:info@module-loader.de">info@module-loader.de</a> senden. Die E-Mail wird um technische Daten zu deinem System ergänzt.</p>

                        <br>
                        <form method="POST" action="?action=reportIssue">
                            <input class="form-control mb-2 w-100" placeholder="Dein Name" type="text" required name="name" id="name">
                            <input class="form-control mb-2 w-100" placeholder="Deine E-Mail Adresse" type="email" required name="email" id="email">
                            <textarea class="form-control mb-2" style="resize: none;" placeholder="Deine Nachricht" rows="8" required name="message"></textarea>
                            <input name="send_mail" type="submit" class="btn btn-primary" value="Nachricht absenden">
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'Footer.tmpl.php' ?>
    </body>
</html>
