<?php

/**
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */

defined('LOADED_FROM_INDEX') && LOADED_FROM_INDEX ?? die('Access denied.');

use RobinTheHood\ModifiedModuleLoaderClient\Config;
?>


<div class="moduleinfo-buttons">

    <div class="btn-group">
        <button type="button" class="btn btn-outline-success">Download & Installieren</button>
        <button type="button" class="btn btn-outline-success dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-expanded="false">
            <span class="sr-only">Toggle Dropdown</span>
        </button>
        <div class="dropdown-menu">
            <a class="dropdown-item" href="#">Download & Installieren</a>
            <a class="dropdown-item" href="#">Download</a>
        </div>
    </div>

    <div class="btn-group">
        <button type="button" class="btn btn-outline-success">Installieren</button>
        <button type="button" class="btn btn-outline-success dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-expanded="false">
            <span class="sr-only">Toggle Dropdown</span>
        </button>
        <div class="dropdown-menu">
            <a class="dropdown-item" href="#">Installieren</a>
            <a class="dropdown-item" href="#">Installieren ohne Abhängigkeiten</a>
            <a class="dropdown-item" href="#">Installieren ohne Abhängigkeiten erzwingen</a>
        </div>
    </div>

    <div class="btn-group">
        <button type="button" class="btn btn-outline-danger">Deinstallieren</button>
        <button type="button" class="btn btn-outline-danger dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-expanded="false">
            <span class="sr-only">Toggle Dropdown</span>
        </button>
        <div class="dropdown-menu">
            <a class="dropdown-item" href="#">Deinstallieren</a>
            <a class="dropdown-item" href="#">Deinstallieren erzwingen</a>
        </div>
    </div>

    <div class="btn-group">
        <button type="button" class="btn btn-outline-danger">Änderungen verwerfen</button>
        <button type="button" class="btn btn-outline-danger dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-expanded="false">
            <span class="sr-only">Toggle Dropdown</span>
        </button>
        <div class="dropdown-menu">
            <a class="dropdown-item" href="#">Änderungen verwerfen</a>
            <a class="dropdown-item" href="#">Änderungen inkl. Templates verwerfen</a>
        </div>
    </div>



    <?php if ($moduleView->isUpdatable() && !$moduleView->isRepairable()) { ?>
        <a class="button button-success" href="<?= $moduleView->getUpdateUrl('moduleInfo') ?>">Update installieren</a>
    <?php } ?>

    <?php if ($moduleView->isRepairable()) { ?>

        <a class="button button-danger" onclick="return confirm('Möchtest du deine Änderungen wirklich rückgängig machen?');" href="<?= $moduleView->getRevertChangesUrl('moduleInfo') ?> ">
            <?php if (Config::getInstallMode() != 'link') {?>
                <i class="fas fa-tools fa-fw"></i>
                Änderungen verwerfen
            <?php } else { ?>
                <i class="fas fa-check fa-fw"></i>
                Änderungen übernehmen (Link-Mode)
                <script>
                    $(document).ready(function() {
                        $('#v-pills-files-tab').tab('show');
                    });
                </script>
            <?php } ?>
        </a>

        <a class="button button-danger" onclick="return confirm('Möchtest du deine Änderungen wirklich rückgängig machen?');" href="<?= $moduleView->getRevertChangesWithTemplateUrl('moduleInfo') ?> ">
            <?php if (Config::getInstallMode() != 'link') {?>
                <i class="fas fa-tools fa-fw"></i>
                Änderungen verwerfen inkl. Templates
            <?php } else { ?>
                <i class="fas fa-check fa-fw"></i>
                Änderungen übernehmen inkl. Templates (Link-Mode)
            <?php } ?>
        </a>
    <?php } ?>

    <?php if ($moduleView->isCompatibleLoadableAndInstallable()) { ?>
        <a class="button button-default" href="<?= $moduleView->getLoadAndInstallUrl('moduleInfo') ?>">Download & Install</a>

    <?php } elseif ($moduleView->isIncompatibleLoadebale()) { ?>
        <a class="button button-default" href="<?= $moduleView->getLoadModuleUrl('moduleInfo') ?>">Download (inkompatible Version)</a>

    <?php } elseif ($moduleView->isUninstallable() && !$moduleView->isRepairable()) { ?>
        <a class="button button-danger" href="<?= $moduleView->getUninstallUrl('moduleInfo') ?>">Deinstallieren</a>

    <?php } elseif ($moduleView->isCompatibleInstallable()) { ?>
        <a class="button button-success" href="<?= $moduleView->getInstallUrl('moduleInfo') ?>">Installieren</a>

    <?php } elseif ($moduleView->isIncompatibleInstallable()) { ?>
        <a class="button button-success" href="<?= $moduleView->getForceInstallUrl('moduleInfo') ?>">Installieren (inkompatible Version)</a>

    <?php } elseif ($moduleView->hasInstalledVersion()) { ?>
        <a class="button button-default" href="<?= $moduleView->getInstalledUrl('moduleInfo') ?>">Zur installierten Version</a>
    <?php } ?>

    <?php if (!$moduleView->isRemote() && $moduleView->isLoaded() && !$moduleView->isInstalled()) { ?>
        <a class="button button-danger" onclick="return confirm('Möchtest du das Modul wirklich entfernen?');" href="<?= $moduleView->getUnloadModuleUrl('moduleInfo') ?>">Modul löschen</a>
    <?php } ?>
</div>