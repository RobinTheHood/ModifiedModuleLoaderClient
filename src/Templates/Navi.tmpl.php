<?php if (!defined('LOADED_FROM_INDEX') || LOADED_FROM_INDEX != 'true') { die('Access denied.'); }?>

<div class="navi">
    <ul class="navi-left">
        <li><a href="?filterModules=all">Alle Module</a></li>
        <li><a href="?filterModules=installed">Installierte Module</a></li>
        <li><a href="?filterModules=loaded">Geladene Module</a></li>
        <li><a href="?filterModules=notloaded">Nicht geladene Module</a></li>
    </ul>

    <ul class="navi-right">
        <li><a href="?action=selfUpdate">System <?php if (isset($checkUpdate) && $checkUpdate) { echo '(1)'; } ?></a></li>
        <li><a href="?action=signOut">Abmelden</a></li>
    </ul>

    <div style="clear: both"></div>
</div>
