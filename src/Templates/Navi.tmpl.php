<?php if (!defined('LOADED_FROM_INDEX') || LOADED_FROM_INDEX != 'true') { die('Access denied.'); }?>

<div class="navi">
    <ul class="navi-left">
        <li><a href="?filterModules=all">Alle</a></li>
        <li><a href="?filterModules=loaded">Geladen</a></li>
        <li><a href="?filterModules=installed">Installiert</a></li>
        <li><a href="?filterModules=updatable">Updates</a></li>
        <li><a href="?filterModules=changed">Geändert</a></li>
        <li><a href="?filterModules=notloaded">Nicht geladen</a></li>
    </ul>

    <ul class="navi-right">
        <li><a href="?action=selfUpdate">System <?php if (isset($checkUpdate) && $checkUpdate) { echo '(1)'; } ?></a></li>
        <li><a href="?action=signOut">Abmelden</a></li>
    </ul>

    <div style="clear: both"></div>
</div>
