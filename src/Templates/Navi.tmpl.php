<?php

/**
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */

defined('LOADED_FROM_INDEX') && LOADED_FROM_INDEX ?? die('Access denied.');

use RobinTheHood\ModifiedModuleLoaderClient\ShopInfo;
?>

<div class="navi">
    <div class="wrapper">
        <div class="logo">
            <picture>
                <source src="src/Templates/Images/mmlc-logo-transparent.png" alt="MMLC Logo">

                <img src="src/Templates/Images/mmlc-logo-transparent.png" alt="MMLC Logo">
            </picture>
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
                <source src="src/Templates/Images/menu.png">
                <source src="src/Templates/Images/menu.svg">

                <img src="src/Templates/Images/menu.png" alt="Menu">
            </picture>

            <ul class="menu-items">
                <li><a class="icon externalLink" href="<?php echo ShopInfo::getAdminUrl() . '/start.php' ?>">zurück zum Shopadmin</a></li> 
                <li><a class="icon help" href="?action=support">Hilfe & Support</a></li>
                <li><a class="icon system" href="?action=selfUpdate">System <span id="systemUpdateCount" class="badge badge-light">0<span></a></li>
                <li><a class="icon settings" href="?action=settings">Einstellungen</a></li>
                <li><a class="icon signOut" href="?action=signOut">Abmelden</a></li>
            </ul>
        </div>
    </div>
</div>
