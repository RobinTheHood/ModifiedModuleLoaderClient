<?php

defined('LOADED_FROM_INDEX') && LOADED_FROM_INDEX ?? die('Access denied.');

use RobinTheHood\ModifiedModuleLoaderClient\Helpers\TemplateHelper;

?>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<?php if (!isset($moduleView) || !isset($module)) {?>
    <title>MMLC - Modified Module Loader Client</title>
    <meta name="description" content="Sie sind auf der Suche nach neuen Laufschuhen? » Wir haben die aktuellsten Modelle für Sie getestet!"/>
    <link rel="canonical" href="https://www.module-loader.de/module" />
<?php } else { ?>
    <title><?= $moduleView->getName() ?> - 📦 Modul fürs modified Shop System</title>
    <meta name="description" content="<?= $module->getShortDescription() ?>"/>
<?php } ?>

<?php
echo TemplateHelper::loadStyleSheet('src/Templates/Styles/bootstrap.min.css');
echo TemplateHelper::loadStyleSheet('src/Templates/Styles/font-awesome-all.css');
echo TemplateHelper::loadStyleSheet('src/Templates/Styles/button.css');
echo TemplateHelper::loadStyleSheet('src/Templates/Styles/lightbox.css');
echo TemplateHelper::loadStyleSheet('src/Templates/Styles/style.css');
?>

<script src="src/Templates/Scripts/jquery-3.3.1.min.js"></script>
