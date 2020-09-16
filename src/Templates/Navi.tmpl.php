<?php if (!defined('LOADED_FROM_INDEX') || LOADED_FROM_INDEX != 'true') { die('Access denied.'); }?>

<?php use RobinTheHood\ModifiedModuleLoaderClient\App; ?>
<?php use RobinTheHood\ModifiedModuleLoaderClient\LazyLoader; ?>
<?php use RobinTheHood\ModifiedModuleLoaderClient\ShopInfo; ?>
<?php use RobinTheHood\ModifiedModuleLoaderClient\DemoMode; ?>

<div class="navi">
    <div class="wrapper">
        <div class="logo">
            <picture>
                <source src="<?php echo App::getUrlRoot();?>/src/Templates/Images/mmlc-logo-transparent.png" alt="MMLC Logo">

                <img src="<?php echo App::getUrlRoot();?>/src/Templates/Images/mmlc-logo-transparent.png" alt="MMLC Logo">
            </picture>
        </div>

        <ul class="navi-main row">
            <?php if (DemoMode::isNotDemo()) { ?>
                <li><a href="<?php echo App::getUrlRoot();?>?filterModules=all">Alle</a></li>
                <li><a href="<?php echo App::getUrlRoot();?>?filterModules=loaded">Geladen</a></li>
                <li><a href="<?php echo App::getUrlRoot();?>?filterModules=installed">Installiert</a></li>
                <li><a href="<?php echo App::getUrlRoot();?>?filterModules=updatable">Updates <span id="moduleUpdateCount" class="badge badge-light">0<span></a></li>
                <li><a href="<?php echo App::getUrlRoot();?>?filterModules=changed">Geändert <span id="moduleChangeCount" class="badge badge-light">0<span></a></li>
                <li><a href="<?php echo App::getUrlRoot();?>?filterModules=notloaded">Nicht geladen</a></li>
            <?php } else { ?>
                <li><a href="<?php echo App::getUrlRoot();?>?filterModules=all">Übersicht aller Module</a></li>
                <li><a href="/">module-loader.de</a></li>
                <li><a href="/imprint.php">Impressum</a></li>
            <?php } ?>
        </ul>

        <?php if (DemoMode::isNotDemo()) { ?>
            <div class="menu">
                <picture class="menu-icon">
                    <source src="<?php echo App::getUrlRoot();?>/src/Templates/Images/menu.png">
                    <source src="<?php echo App::getUrlRoot();?>/src/Templates/Images/menu.svg">

                    <img src="<?php echo App::getUrlRoot();?>/src/Templates/Images/menu.png" alt="Menu">
                </picture>

                <ul class="menu-items">
                    <li><a class="icon externalLink" href="<?php echo '//' . rtrim($_SERVER['HTTP_HOST'], '/') . '/' . ShopInfo::getAdminDir() . '/start.php' ?>">zurück zum Shopadmin</a></li>
                    <li><a class="icon help" href="<?php echo App::getUrlRoot();?>/?action=support">Hilfe & Support</a></li>
                    <li><a class="icon system" href="<?php echo App::getUrlRoot();?>/?action=selfUpdate">System <span id="systemUpdateCount" class="badge badge-light">0<span></a></li>
                    <li><a class="icon settings" href="<?php echo App::getUrlRoot();?>/?action=settings">Einstellungen</a></li>
                    <li><a class="icon signOut" href="<?php echo App::getUrlRoot();?>/?action=signOut">Abmelden</a></li>
                </ul>
            </div>
        <?php } ?>
    </div>
</div>

<?php echo LazyLoader::loadModuleUpdateCount('#moduleUpdateCount') ?>
<?php echo LazyLoader::loadModuleChangeCount('#moduleChangeCount') ?>
<?php echo LazyLoader::loadSystemUpdateCount('#systemUpdateCount') ?>
