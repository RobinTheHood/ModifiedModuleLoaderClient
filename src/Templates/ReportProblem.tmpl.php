<?php if (!defined('LOADED_FROM_INDEX') || LOADED_FROM_INDEX != 'true') { die('Access denied.'); }?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <?php include 'Head.tmpl.php' ?>
    </head>
    <body>
        <?php include 'Navi.tmpl.php' ?>
        <div class="content">
        <?php echo RobinTheHood\ModifiedModuleLoaderClient\Notification::renderFlashMessages() ?>
            <div class="container">
              <div class="row">
                <div class="offset-4 col-4">
                  <form method="POST" action="?action=reportProblem">
                    <input class="mb-2 w-100" placeholder="Ihr Name" type="text" required name="name" id="name">
                    <input class="mb-2 w-100" placeholder="Ihre E-mail" type="email" required name="email" id="email">
                    <textarea style="resize:none;" placeholder="Ihre Nachricht" rows="6" required class="mb-2 form-control" name="message"></textarea>
                    <input name="send_mail" type="submit" class="button button-default" value="Nachricht absenden">
                  </form>
                </div>
              </div>
            </div>
        </div>
        <?php include 'Footer.tmpl.php' ?>
    </body>
</html>
