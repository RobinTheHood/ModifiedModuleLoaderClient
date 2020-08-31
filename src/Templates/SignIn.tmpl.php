<?php if (!defined('LOADED_FROM_INDEX') || LOADED_FROM_INDEX != 'true') { die('Access denied.'); }?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <?php include 'Head.tmpl.php' ?>
    </head>

    <body id="signIn">
        <div class="content">
            <div class="spacer top"></div>

            <img src="src/Templates/Images/mmlc-logo-transparent.png" alt="MMLC Logo" class="logo">

            <div class="signin-frame">
                <form action="?action=signIn" method="post">
                    <h1>MMLC Anmeldung</h1>

                    <fieldset>
                        <label for="username">
                            <picture>
                                <source src="src/Templates/Images/user.png">

                                <img src="src/Templates/Images/user.png">
                            </picture>
                        </label>
                        <input id="username" type="text" name="username" value="<?php echo empty($_POST['username']) ? '' :  $_POST['username'] ?>" placeholder="Benutzername">
                    </fieldset>

                    <fieldset>
                        <label for="password">
                            <picture>
                                <source src="src/Templates/Images/password.png">

                                <img src="src/Templates/Images/password.png">
                            </picture>
                        </label>

                        <input id="password" type="password" name="password" value="" placeholder="Passwort">
                    </fieldset>

                    <input type="submit" value="Anmelden" class="primary">
                </form>
            </div>

            <div class="spacer bottom"></div>
        </div>
    </body>
</html>
