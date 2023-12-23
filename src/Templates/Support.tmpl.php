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
            <h1>Hilfe & Support</h1>

            <?= $notificationView->renderFlashMessages() ?>

            <section>
                <h2>Anleitung</h2>
                <p>Wenn du Hilfe brauchst und eine Anleitung für den MMLC benötigst, findest du diese unter: <a target="_blank" href="https://module-loader.de/documentation.php">module-loader.de/documentation.php</a>.
            </section>

            <section>
                <h2>Community / Forum / Chat</h2>
                <p>
                    Auf unserem <a target="_blank" href="https://discord.gg/9NqwJqP">Discord #mmlc DE/EN</a> Community Server beantworten wir dir deine Fragen sehr gerne, wenn du zusätzliche Hilfe benötigst. Auf Discord kannst du dich auch mit anderen Entwickelrn und Usern austauschen. Wenn du dich fragst, wieso wir Discord verwenden, findest du <a target="_blank" href="https://discord.com/open-source">hier die Antwort</a> auf deine Frage.
                </p>
            </section>

            <section>
                <h2>Support-Anfrage an die MMLC Entwickler</h2>
                <p>
                    Du kannst uns auch direkt eine Nachricht zukommen lassen mit deinen Fragen. Die Nachricht wird automatisch um einige technische Informationen zu deinem System ergänzt, was uns die Bearbeitung deiner E-Mail erleichtert. Folgende Daten erhalten wir von deinem System:
                </p>

                <ul>
                    <li>Die Domain unter der dein MMLC läuft</li>
                    <li>Die Version von Modified die du verwendest</li>
                    <li>Die Version des MMLC die du verwendest</li>
                    <li>Informationen zu deinem Browser</li>
                    <li>Die Version von PHP unter dem dein MMLC läuft</li>
                </ul>

                <p>
                    <a href="?action=reportIssue">Zum Formular und eine Nachricht verfassen</a>
                </p>
            </section>

            <section>
                <h2>Modul-Entwickler werden</h2>
                <p>
                    Wir freuen uns sehr, wenn du dich dafür interessierst selber Module für den MMLC zu entwickeln. Wenn du selber Module für den MMLC schreiben möchtest, findest hierzu Anleitungen und Tutorials unter: <a target="_blank" href="https://module-loader.de/docs">module-loader.de/docs</a>.
                </p>
            </section>

            <section>
                <h2>MMLC-Entwickler werden / Contributing</h2>

                <p>
                    Wir freuen uns sehr, dass du dich für den MMLC interessierst und Lust hast dich am MMLC zu beteiligen. Es gibt viele Dinge zu denen du hier beitragen kannst. Sei es die Dokumentation zu erweitern oder den Programmcode zu verbessern und natürlich all die Dinge, die hier noch nicht aufgeführt sind. Wir freuen uns auch über kleine Beiträge.
                </p>

                <p>
                    Den MMLC findest du als Open-Source-Projekt auf <a target="_blank" href="https://github.com/RobinTheHood/ModifiedModuleLoaderClient">GitHub</a>. Hier kannst du Wünsche und Fehler als Issues eintragen. Oder du machst einen Pull request, wenn du bereits konkrete Quellcodeverbesserungen einreichen möchtest. Wenn du Hilfe brauchst, kannst du uns auch gerne bei <a target="_blank" href="https://discord.gg/9NqwJqP">Discord #mmlc</a> nach Rat fragen.
                </p>

                <p>
                    Mehr Information für MMLC-Entwickler findest du unter: <a target="_blank" href="https://module-loader.de/docs">module-loader.de/docs</a>.
                </p>
            </section>
        </div>

        <?php include 'Footer.tmpl.php' ?>
    </body>
</html>
