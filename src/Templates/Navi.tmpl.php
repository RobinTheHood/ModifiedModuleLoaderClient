<?php if (!defined('LOADED_FROM_INDEX') || LOADED_FROM_INDEX != 'true') { die('Access denied.'); }?>

<?php use RobinTheHood\ModifiedModuleLoaderClient\LazyLoader; ?>

<div class="navi">
    <div class="wrapper">
        <div class="logo">
            <img src="src/images/mmlc-logo-transparent.png" alt="MMLC Logo">
        </div>

        <ul class="navi-main row">
            <li><a href="?filterModules=all">Alle</a></li>
            <li><a href="?filterModules=loaded">Geladen</a></li>
            <li><a href="?filterModules=installed">Installiert</a></li>
            <li><a href="?filterModules=updatable">Updates <span id="moduleUpdateCount" class="badge badge-light">0<span></a></li>
            <li><a href="?filterModules=changed">Geändert <span id="moduleChangeCount" class="badge badge-light">0<span></a></li>
            <li><a href="?filterModules=notloaded">Nicht geladen</a></li>
        </ul>

        <div></div>

        <div class="menu">
            <picture class="menu-icon">
                <source src="src/images/settings.png">
                <source src="src/images/settings.svg">

                <img src="src/images/settings.png" alt="Menu">
            </picture>

            <ul class="menu-items">
                <li><a href="<?php echo '//' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . 'admin/start.php'; ?>">zum Shop</a></li>
                <li><a href="?action=support">Hilfe</a></li>
                <li><a href="?action=selfUpdate">System <span id="systemUpdateCount" class="badge badge-light">0<span></a></li>
                <li><a href="?action=signOut">Abmelden</a></li>
            </ul>
        </div>
    </div>
</div>

<?php echo LazyLoader::loadModuleUpdateCount('#moduleUpdateCount') ?>
<?php echo LazyLoader::loadModuleChangeCount('#moduleChangeCount') ?>
<?php echo LazyLoader::loadSystemUpdateCount('#systemUpdateCount') ?>
