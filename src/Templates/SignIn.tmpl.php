<?php if (!defined('LOADED_FROM_INDEX') || LOADED_FROM_INDEX != 'true') { die('Access denied.'); }?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <?php include 'Head.tmpl.php' ?>
    </head>

    <body>
        <div class="content">
            <div class="signin-frame">
                <form action="?action=signIn" method="post">
                    <label for="username">Username:</label>
                    <input type="text" name="username" value="<?php echo empty($_POST['username']) ? '' :  $_POST['username'] ?>">

                    <label for="password">Passwort:</label>
                    <input type="password" name="password" value="">

                    <input type="submit" value=" Anmelden ">
                </form>
            </div>
        </div>
    </body>
</html>
