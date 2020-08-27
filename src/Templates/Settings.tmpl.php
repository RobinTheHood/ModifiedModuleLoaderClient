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
            <?php
            /**
             * This is all just temporary and for testing purposes
             */
            $pair = [
                ['username' => 'newusername'],
                ['password' => 'newpaddowrd']
            ];

            $string = file_get_contents('D:\WampServer\www/modified-shop/ModifiedModuleLoaderClient/config/config.php');

            for ($i=0; $i < count($pair); $i++) {
                foreach ($pair[$i] as $key => $value) {

                    $matches;
                    $regex = '/\'(' . $key . ')\'[ ]*=>[ ]*\'(.+)\'/';

                    preg_match($regex, $string, $matches);
                    $string = str_replace($matches[2], $value, $string);
                    var_dump($string);
                }
            }

            //Config::setOptions(['test' => 'helloWorld']);
            ?>
        </div>

        <?php include 'Footer.tmpl.php' ?>
    </body>
</html>
