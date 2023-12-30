<?php

/**
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */

defined('LOADED_FROM_INDEX') && LOADED_FROM_INDEX ?? die('Access denied.');

use RobinTheHood\ModifiedModuleLoaderClient\Config;
use RobinTheHood\ModifiedModuleLoaderClient\ViewModels\ModuleViewModel;
use RobinTheHood\ModifiedModuleLoaderClient\ViewModels\ButtonViewModel as Button;

/** @var ModuleViewModel $moduleView */

$buttonInfo = Button::create(Button::PRIMARY)
    ->addAction('Zur installierten Version', $moduleView->getInstalledUrl('moduleInfo'));

$buttonPullAndInstall = Button::create(Button::PRIMARY)
    ->addAction('Download & Installieren', $moduleView->getLoadAndInstallUrl('moduleInfo'))
    ->addAction('Download', $moduleView->getLoadModuleUrl('moduleInfo'));

$buttonPull = Button::create(Button::PRIMARY)
    ->addAction('Download', $moduleView->getLoadModuleUrl('moduleInfo'));

$buttonInstall = Button::create(Button::SUCCESS)
    ->addAction('Installieren', $moduleView->getInstallUrl('moduleInfo'))
    //->addAction('Installieren ohne Abhängigkeiten', '#')
    ->addAction('Installieren ohne Abhängigkeiten erzwingen', $moduleView->getForceInstallUrl('moduleInfo'));

$buttonUpdate = Button::create(Button::SUCCESS)
    ->addAction('Update installieren', $moduleView->getUpdateUrl('moduleInfo'))
    ->addAction('Update installieren ohne Abhängigkeiten erzwingen', $moduleView->getForceUpdateUrl('moduleInfo'));

$buttonDiscard = Button::create(Button::DANGER)
    ->addConfirmAction(
        'Änderungen verwerfen',
        $moduleView->getRevertChangesUrl('moduleInfo'),
        'Möchtest du deine Änderungen wirklich rückgängig machen?'
    )
    ->addConfirmAction(
        'Änderungen inkl. Templates verwerfen',
        $moduleView->getRevertChangesWithTemplateUrl('moduleInfo'),
        'Möchtest du deine Änderungen wirklich rückgängig machen?'
    );

$buttonDiscardLink = Button::create(Button::WARNING)
    ->addAction(
        'Änderungen übernehmen',
        $moduleView->getRevertChangesUrl('moduleInfo')
    )
    ->addAction(
        'Änderungen inkl. Templates übernehmen',
        $moduleView->getRevertChangesWithTemplateUrl('moduleInfo')
    );

$buttonUninstall = Button::create(Button::DANGER)
    ->addAction('Deinstallieren', $moduleView->getUninstallUrl('moduleInfo'))
    ->addAction('Deinstallieren erzwingen', $moduleView->getForceUninstallUrl('moduleInfo'));

$buttonDelete = Button::create(Button::DANGER)
    ->addConfirmAction('Löschen', $moduleView->getUnloadModuleUrl('moduleInfo'), 'Möchtest du das Modul wirklich löschen?');

$jsGoToFilesTab = "
    <script>
        $(document).ready(function() {
            $('#v-pills-files-tab').tab('show');
        });
    </script>
";
?>

<div class="moduleinfo-buttons module-action-button">
    <?php
    if ($moduleView->isUpdatable() && !$moduleView->isRepairable()) {
        echo $buttonUpdate;
    } elseif ($moduleView->hasInstalledVersion()) {
        echo $buttonInfo;
    }

    if ($moduleView->isRepairable()) {
        if (Config::getInstallMode() != 'link') {
            echo $buttonDiscard;
            echo $buttonUninstall;
        } else {
            echo $buttonDiscardLink;
            echo $buttonUninstall;
            echo $jsGoToFilesTab;
        }
    }

    if ($moduleView->isCompatibleLoadableAndInstallable()) {
        echo $buttonPullAndInstall;
    } elseif ($moduleView->isIncompatibleLoadebale()) {
        echo $buttonPull;
    } elseif ($moduleView->isUninstallable() && !$moduleView->isRepairable()) {
        echo $buttonUninstall;
    } elseif ($moduleView->isCompatibleInstallable()) {
        echo $buttonInstall;
    } elseif ($moduleView->isIncompatibleInstallable()) {
        echo $buttonInstall;
    }

    if (!$moduleView->isRemote() && $moduleView->isLoaded() && !$moduleView->isInstalled()) {
        echo $buttonDelete;
    }
    ?>
</div>