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
                <source src="<?= MMLC_ROOT ?>src/Templates/Images/mmlc-logo-transparent.png" alt="MMLC Logo">

                <img src="<?= MMLC_ROOT ?>src/Templates/Images/mmlc-logo-transparent.png" alt="MMLC Logo">
            </picture>
        </div>

        <ul class="navi-main row">
            <li><a href="<?= MMLC_ROOT ?>?filterModules=all">Übersicht aller Module</a></li>
            <li><a href="https://module-loader.de">module-loader.de</a></li>
            <li><a href="https://module-loader.de/imprint.php">Impressum</a></li>
        </ul>
    </div>
</div>
