<?php if (!defined('LOADED_FROM_INDEX') || LOADED_FROM_INDEX != 'true') { die('Access denied.'); }?>

<div class="navi">
    <ul class="navi-left">
        <li><a href="?filterModules=all">Alle</a></li>
        <li><a href="?filterModules=loaded">Geladen</a></li>
        <li><a href="?filterModules=installed">Installiert</a></li>
        <li><a href="?filterModules=updatable">Updates <?php if (isset($updateCount) && $updateCount) { echo "<span class=\"badge badge-light\">$updateCount<span>"; }?></a></li>
        <li><a href="?filterModules=changed">Geändert <?php if (isset($repairalbeCount) && $repairalbeCount) { echo "<span class=\"badge badge-light\">$repairalbeCount<span>"; }?></a></li>
        <li><a href="?filterModules=notloaded">Nicht geladen</a></li>
    </ul>

    <ul class="navi-right">
        <li><a href="?action=selfUpdate">System <?php if (isset($checkUpdate) && $checkUpdate) { echo "<span class=\"badge badge-light\">1<span>"; } ?></a></li>
        <li><a href="?action=signOut">Abmelden</a></li>
    </ul>

    <div style="clear: both"></div>
</div>
