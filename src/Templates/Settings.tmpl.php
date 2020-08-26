<?php if (!defined('LOADED_FROM_INDEX') || LOADED_FROM_INDEX != 'true') { die('Access denied.'); }?>
<?php global $configuration; ?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <?php include 'Head.tmpl.php' ?>
    </head>

    <body>
        <?php include 'Navi.tmpl.php' ?>
                
        <div class="content">
            <h1>Einstellungen</h1>

            <table>
                <?php foreach ($configuration as $key => $value) { ?>
                    <tr>
                        <td class="name"><?php echo $key; ?></td>
                        <td class="value"><input type="text" name="<?php echo $key; ?>" value="<?php echo $value; ?>"></td>
                    </tr>
                <?php } ?>
                <?php  ?>
            </table>
        </div>

        <?php include 'Footer.tmpl.php' ?>
    </body>
</html>
