<?php if (!defined('LOADED_FROM_INDEX') || LOADED_FROM_INDEX != 'true') { die('Access denied.'); }?>

<?php use RobinTheHood\ModifiedModuleLoaderClient\App; ?>
<?php use RobinTheHood\ModifiedModuleLoaderClient\LinkBuilder; ?>
<?php use RobinTheHood\ModifiedModuleLoaderClient\Helpers\TemplateHelper; ?>

<meta charset="utf-8">

<?php if (!empty($module)) { ?>
    <title><?php echo $module->getName() ?> - Modul für modified Shop - MMLC</title>
    <meta name="description" content="<?php echo $module->getShortDescription() ?>">
    <link rel="canonical" href="http://www.module-loader.de<?php echo LinkBuilder::getModulUrlByValue($archiveName) ?>">
<?php } else { ?>
    <title>MMLC - Modified Module Loader Client</title>
    <meta name="description" content="Übersicht der Module für die modified eCommerce Shop Software">
<?php } ?>

<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<?php
echo TemplateHelper::loadStyleSheet('/src/Templates/Styles/bootstrap.min.css');
echo TemplateHelper::loadStyleSheet('/src/Templates/Styles/font-awesome-all.css');
echo TemplateHelper::loadStyleSheet('/src/Templates/Styles/button.css');
echo TemplateHelper::loadStyleSheet('/src/Templates/Styles/lightbox.css');
echo TemplateHelper::loadStyleSheet('/src/Templates/Styles/style.css');
?>

<script src="<?php echo App::getUrlRoot();?>/src/Templates/Scripts/jquery-3.3.1.min.js"></script>
