<?php if (!defined('LOADED_FROM_INDEX') || LOADED_FROM_INDEX != 'true') { die('Access denied.'); }?>

<?php use RobinTheHood\ModifiedModuleLoaderClient\LazyLoader; ?>

<div class="navi">
    <ul class="navi-left">
        <li><a href="?filterModules=all">Alle</a></li>
        <li><a href="?filterModules=loaded">Geladen</a></li>
        <li><a href="?filterModules=installed">Installiert</a></li>
        <li><a href="?filterModules=updatable">Updates <span style="display: none" id="moduleUpdateCount" class="badge badge-light">0<span></a></li>
        <li><a href="?filterModules=changed">Geändert <span style="display: none" id="moduleChangeCount" class="badge badge-light">0<span></a></li>
        <li><a href="?filterModules=notloaded">Nicht geladen</a></li>
    </ul>

    <ul class="navi-right">
        <li><a href="<?php echo '//' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . 'admin/start.php'; ?>">zurück zum Shop</a></li>
        <li><a href="?action=selfUpdate">System <span style="display: none" id="systemUpdateCount" class="badge badge-light">0<span></a></li>
        <li><a href="?action=reportProblem">Nachricht an Entwickler senden</a></li>
        <li><a href="?action=signOut">Abmelden</a></li>
    </ul>

    <div style="clear: both"></div>
</div>

<?php echo LazyLoader::loadModuleUpdateCount('#moduleUpdateCount') ?>
<?php echo LazyLoader::loadModuleChangeCount('#moduleChangeCount') ?>
<?php echo LazyLoader::loadSystemUpdateCount('#systemUpdateCount') ?>
