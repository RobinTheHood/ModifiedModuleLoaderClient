<?php

/**
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */

defined('LOADED_FROM_INDEX') && LOADED_FROM_INDEX ?? die('Access denied.');
?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <?php include 'Head.tmpl.php' ?>

        <script>
            /**
             * Animate sign in background
             *
             * This does not belong here.
             * Please create a new js file and embed it properly.
             * don't forget to "use strict";
             */
            window.addEventListener('DOMContentLoaded', (event) => {
                let signIn = document.getElementById('signIn');
                let deg = 45;
                let tick = () => {
                    deg = (deg % 360) + 0.2;
                    signIn.style.background = `background linear-gradient(${deg}deg, #1cb5e0 0%, #000851 100%)`;
                };
                setInterval(tick, 100);
            });
        </script>
    </head>

    <body id="signIn">
        <div class="content">
            <div class="test">
                <img src="src/Templates/Images/mmlc-logo-transparent.png" alt="MMLC Logo" class="logo">

                <div class="signin-frame">
                    <form action="?action=signIn" method="post">
                        <h1>MMLC Anmeldung</h1>

                        <div>
                            <label for="username">
                                <picture>
                                    <source src="src/Templates/Images/user.png">

                                    <img src="src/Templates/Images/user.png">
                                </picture>
                            </label>
                            <input id="username" type="text" name="username" value="<?php echo empty($_POST['username']) ? '' :  $_POST['username'] ?>" placeholder="Benutzername">
                        </div>

                        <div>
                            <label for="password">
                                <picture>
                                    <source src="src/Templates/Images/password.png">

                                    <img src="src/Templates/Images/password.png">
                                </picture>
                            </label>

                            <input id="password" type="password" name="password" value="" placeholder="Passwort">
                        </div>

                        <input type="submit" value="Anmelden" class="primary">
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
