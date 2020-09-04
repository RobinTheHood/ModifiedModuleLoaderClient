<?php if (!defined('LOADED_FROM_INDEX') || LOADED_FROM_INDEX != 'true') { die('Access denied.'); }?>

<meta charset="utf-8">
<title>MMLC - Modified Module Loader Client</title>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<?php
use RobinTheHood\ModifiedModuleLoaderClient\Helpers\TemplateHelper;

echo TemplateHelper::loadStyleSheet('src/Templates/Styles/bootstrap.min.css');
echo TemplateHelper::loadStyleSheet('src/Templates/Styles/font-awesome-all.css');
echo TemplateHelper::loadStyleSheet('src/Templates/Styles/button.css');
echo TemplateHelper::loadStyleSheet('src/Templates/Styles/lightbox.css');
echo TemplateHelper::loadStyleSheet('src/Templates/Styles/style.css');
?>

<script src="src/Templates/Scripts/jquery-3.3.1.min.js"></script>
