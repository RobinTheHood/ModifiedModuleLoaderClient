<?php
defined('LOADED_FROM_INDEX') && LOADED_FROM_INDEX ?? die('Access denied.');

use RobinTheHood\ModifiedModuleLoaderClient\ModuleStatus;
use RobinTheHood\ModifiedModuleLoaderClient\Category;
use RobinTheHood\ModifiedModuleLoaderClient\Notification;
use RobinTheHood\ModifiedModuleLoaderClient\LazyLoader;
use RobinTheHood\ModifiedModuleLoaderClient\ShopInfo;
use RobinTheHood\ModifiedModuleLoaderClient\Config;
use RobinTheHood\ModifiedModuleLoaderClient\ViewModels\ModuleViewModel;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher;

$moduleView = new ModuleViewModel($module);
?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <?php include 'Head.tmpl.php' ?>
        <link rel="stylesheet" href="src/Templates/Styles/github.css">
        <script src="src/Templates/Scripts/highlight.pack.js"></script>
        <script>hljs.initHighlightingOnLoad();</script>
    </head>

    <body>
        <?php include 'Navi.tmpl.php' ?>

        <div class="block">
            <div class="content">
                <?= Notification::renderFlashMessages() ?>

                <div class="row">
                    <div class="col">
                        <div class="module-title">
                            <img src="<?= $module->getIconUri(); ?>">

                            <h1><?= $module->getName() ?></h1>
                        </div>

                        <?php if ($module->getImageUris()) { ?>
                            <div class="module-previews">
                            <?php foreach ($module->getImageUris() as $image) { ?>
                                <div class="preview">
                                    <a href="<?= $image ?>" data-lightbox="show-1" data-title="<?= $module->getName() ?>">
                                        <img src="<?= $image ?>">
                                    </a>
                                </div>
                            <?php } ?>
                            </div>
                        <?php } ?>

                        <?php if (ModuleStatus::isRepairable($module)) { ?>
                            <div class="alert alert-warning" role="alert">
                                <i class="fas fa-exclamation-triangle fa-fw"></i>
                                Einige Dateien befinden sich nicht mehr im Originalzustand. Möglicherweise hast du an diesen
                                Anpassungen vorgenommen. <strong>Deinstallation</strong> und <strong>Update</strong> stehen dir nur bei unveränderten Modulen zur
                                Verfügung, damit deine Arbeit nicht verloren geht. <a href="#v-pills-tabContent" onclick="$('#v-pills-files-tab').tab('show');">Alle Änderungen ansehen</a>.
                            </div>
                        <?php } ?>

                        <?php if (!ModuleStatus::isLoadable($module)) { ?>
                            <div class="alert alert-primary" role="alert">
                                <i class="fas fa-info-circle fa-fw"></i>
                                Um dieses Modul zu verwenden, nimm bitte Kontakt zum Entwickler auf. Der Entwickler kann dir das Modul (z. B. nach einem Kauf) freischalten.
                            </div>
                        <?php } ?>

                        <?php if (!$module->isCompatible()) { ?>
                            <div class="alert alert-warning" role="alert">
                                <i class="fas fa-exclamation-triangle fa-fw"></i>
                                Dieses Modul wurde noch nicht mit deiner Version von modified getestet. Du hast modifed <strong><?= ShopInfo::getModifiedVersion()?></strong> installiert.
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="moduleinfo-buttons">
                <?php if (ModuleStatus::isUpdatable($module) && !ModuleStatus::isRepairable($module)) { ?>
                    <a class="button button-success" href="<?= $moduleView->getUpdateUrl('moduleInfo') ?>">Update installieren</a>
                <?php } ?>

                <?php if (ModuleStatus::isRepairable($module)) { ?>
                    <a class="button button-danger" onclick="return confirm('Möchtest du deine Änderungen wirklich rückgängig machen?');" href="
                    <?= $moduleView->getInstallUrl('moduleInfo') ?> ">
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
                <?php } ?>

                <?php if (ModuleStatus::isCompatibleLoadebaleAndInstallable($module)) { ?>
                    <a class="button button-default" href="<?= $moduleView->getLoadAndInstallUrl('moduleInfo') ?>">Download & Install</a>

                <?php } elseif (ModuleStatus::isUncompatibleLoadebale($module)) { ?>
                    <a class="button button-default" href="<?= $moduleView->getLoadModuleUrl('moduleInfo') ?>">Download (inkompatible Version)</a>

                <?php } elseif (ModuleStatus::isUninstallable($module) && !ModuleStatus::isRepairable($module)) { ?>
                    <a class="button button-danger" href="<?= $moduleView->getUninstallUrl('moduleInfo') ?>">Deinstallieren</a>

                <?php } elseif (ModuleStatus::isCompatibleInstallable($module)) { ?>
                    <a class="button button-success" href="<?= $moduleView->getInstallUrl('moduleInfo') ?>">Installieren</a>

                <?php } elseif (ModuleStatus::isUncompatibleInstallable($module)) { ?>
                    <a class="button button-success" href="<?= $moduleView->getInstallUrl('moduleInfo') ?>">Installieren (inkompatible Version)</a>

                <?php } elseif ($installedModule = $module->getInstalledVersion()) { ?>
                    <?php if ($installedModule->getVersion() != $module->getVersion()) { ?>
                        <a class="button button-default" href="<?= $moduleView->getModuleInfoUrl('moduleInfo') ?>">Zur installierten Version</a>
                    <?php } ?>
                <?php } ?>

                <?php if (!$module->isRemote() && $module->isLoaded() && !$module->isInstalled()) { ?>
                    <a class="button button-danger" onclick="return confirm('Möchtest du das Modul wirklich entfernen?');" href="<?= $moduleView->getUnloadModuleUrl('moduleInfo') ?>">Modul löschen</a>
                <?php } ?>
            </div>

            <div class="row">
                <div class="col-3">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active show" id="v-pills-overview-tab" data-toggle="pill" href="#v-pills-overview" role="tab" aria-controls="v-pills-overview" aria-selected="true">Übersicht</a>
                        <a class="nav-link" id="v-pills-install-tab" data-toggle="pill" href="#v-pills-install" role="tab" aria-controls="v-pills-install" aria-selected="false">Installation</a>
                        <a class="nav-link" id="v-pills-usage-tab" data-toggle="pill" href="#v-pills-usage" role="tab" aria-controls="v-pills-usage" aria-selected="false">Bedienung</a>
                        <a class="nav-link" id="v-pills-changes-tab" data-toggle="pill" href="#v-pills-changes" role="tab" aria-controls="v-pills-changes" aria-selected="false">Änderungsprotokoll</a>
                        <a class="nav-link" id="v-pills-details-tab" data-toggle="pill" href="#v-pills-details" role="tab" aria-controls="v-pills-details" aria-selected="false">Details</a>
                        <?php if (ModuleStatus::isRepairable($module)) { ?>
                            <a class="nav-link" id="v-pills-files-tab" data-toggle="pill" href="#v-pills-files" role="tab" aria-controls="v-pills-files" aria-selected="false">Geänderte Dateien</a>
                        <?php } ?>
                    </div>
                </div>

                <div class="col-9">
                    <div class="tab-content" id="v-pills-tabContent">
                        <div class="tab-pane fade active show" id="v-pills-overview" role="tabpanel" aria-labelledby="v-pills-overview-tab">
                            <div class="infos">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <td>Version</td>
                                            <td><?= $module->getVersion(); ?></td>
                                        </tr>

                                        <tr>
                                            <td>Preis</td>
                                            <td><?= $module->getPriceFormated(); ?></td>
                                        </tr>

                                        <tr>
                                            <td>Kompatibel mit Modified</td>
                                            <td>
                                                <?php if ($module->getModifiedCompatibility()) { ?>
                                                    <?php foreach ($module->getModifiedCompatibility() as $version) { ?>
                                                        <?php
                                                        $badgeClasses = [
                                                            'badge'
                                                        ];
                                                        $badgeInnerHTML = '';
                                                        $badgeInnerHTML .= $version;

                                                        if ($version == ShopInfo::getModifiedVersion()) {
                                                            $badgeClasses[] = 'badge-primary';
                                                            $badgeInnerHTML .= ' (installiert)';
                                                        } else {
                                                            $badgeClasses[] = 'badge-secondary';
                                                        }
                                                        ?>

                                                        <span class="<?= implode(' ', $badgeClasses); ?>"><?= $badgeInnerHTML; ?></span>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    unbekannt
                                                <?php } ?>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Entwickler</td>
                                            <td>
                                                <?php if ($module->getDeveloper() && $module->getDeveloperWebsite()) { ?>
                                                    <a target="_blank" href="<?= $module->getDeveloperWebsite() ?>"><?= $module->getDeveloper() ?></a>
                                                <?php } elseif ($module->getDeveloper()) { ?>
                                                    <?= $module->getDeveloper() ?>
                                                <?php } else { ?>
                                                    unbekannter Entwickler
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="markdown">
                                <h2>Beschreibung</h2>
                                <p>
                                    <?php if ($module->getDescription()) { ?>
                                        <?= $module->getDescription() ?>
                                    <?php } else { ?>
                                        Keine Beschreibung vorhanden.
                                    <?php } ?>
                                </p>
                            </div>
                        </div>                        

                        <div class="tab-pane fade" id="v-pills-install" role="tabpanel" aria-labelledby="v-pills-install-tab">
                            <div class="markdown">
                                Wird geladen. Bitte warten...
                            </div>
                        </div>
                        

                        <div class="tab-pane fade" id="v-pills-usage" role="tabpanel" aria-labelledby="v-pills-usage-tab">
                            <div class="markdown">
                                Wird geladen. Bitte warten...
                            </div>
                        </div>
                        

                        <div class="tab-pane fade" id="v-pills-changes" role="tabpanel" aria-labelledby="v-pills-changes-tab">
                            <div class="markdown changelog">
                                Wird geladen. Bitte warten...
                            </div>
                        </div>

                        <div class="tab-pane fade" id="v-pills-details" role="tabpanel" aria-labelledby="v-pills-details-tab">
                            <div class="infos">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <td>Archivname</td>
                                            <td><?= $module->getArchiveName(); ?></td>
                                        </tr>

                                        <tr>
                                            <td>Version</td>
                                            <td><?= $module->getVersion(); ?></td>
                                        </tr>

                                        <tr>
                                            <td>Kompatibel mit Modified</td>
                                            <td>
                                                <?php if ($module->getModifiedCompatibility()) { ?>
                                                    <?php foreach ($module->getModifiedCompatibility() as $version) { ?>
                                                        <span class="badge badge-secondary"><?= $version; ?></span>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    unbekannt
                                                <?php } ?>
                                            </td>
                                        </tr>

                                        <?php if ($module->getTags()) { ?>
                                            <tr>
                                                <td>Tags</td>
                                                <td>
                                                    <?php foreach (explode(',', $module->getTags()) as $tag) { ?>
                                                        <span class="badge badge-secondary"><?= trim($tag); ?></span>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php } ?>

                                        <tr>
                                            <td>Entwickler</td>
                                            <td>
                                                <?php if ($module->getDeveloper() && $module->getDeveloperWebsite()) { ?>
                                                    <a target="_blank" href="<?= $module->getDeveloperWebsite() ?>"><?= $module->getDeveloper() ?></a>
                                                <?php } elseif ($module->getDeveloper()) { ?>
                                                    <?= $module->getDeveloper() ?>
                                                <?php } else { ?>
                                                    unbekannter Entwickler
                                                <?php } ?>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Alle Versionen</td>
                                            <td>
                                                <?php foreach ($module->getVersions() as $moduleVersion) {?>
                                                    <a href="?action=moduleInfo&archiveName=<?= $moduleVersion->getArchiveName() ?>&version=<?= $moduleVersion->getVersion()?>"><?= $moduleVersion->getVersion(); ?></a>
                                                    <?php if ($moduleVersion->isInstalled()) { ?>
                                                        <span>installiert</span>
                                                    <?php } elseif ($moduleVersion->isLoaded()) { ?>
                                                        <span>geladen</span>
                                                    <?php } else { ?>
                                                        <span>nicht geladen</span>
                                                    <?php } ?>
                                                    <br>
                                                <?php } ?>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Benötigt</td>
                                            <td>
                                                <?php if ($module->getRequire()) { ?>
                                                    <?php foreach ($module->getRequire() as $archiveName => $version) { ?>
                                                        <a href="?action=moduleInfo&archiveName=<?= $archiveName?>"><?= $archiveName?></a><span>: <?= $version ?></span><br>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    keine Abhängigkeit vorhanden
                                                <?php } ?>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Benutzt von</td>
                                            <td>
                                                <?php if ($module->getUsedBy()) { ?>
                                                    <?php foreach ($module->getUsedBy() as $usedBy) { ?>
                                                        <a href="?action=moduleInfo&archiveName=<?= $usedBy->getArchiveName()?>&version=<?= $usedBy->getVersion() ?>"><?= $usedBy->getArchiveName()?></a><span>: <?= $usedBy->getVersion() ?></span><br>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    wird von keinem Modul verwendet
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <hr>

                                <div id="readme" style="padding-bottom: 30px">
                                    <div class="markdown">
                                        README.md Wird geladen. Bitte warten...
                                    </div>
                                 </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="v-pills-files" role="tabpanel" aria-labelledby="v-pills-files-tab">
                            <h3>Geänderte Dateien</h3>

                            <?php if ($module->isInstalled() && $module->isChanged()) { ?>
                                    <?php foreach ($module->getChancedFiles() as $file => $mode) { ?>
                                        <?php $changes = htmlentities(ModuleHasher::getFileChanges($module, $file, $mode)); ?>

                                        <div><?= $file ?><span>: <?= $mode ?></span></div>
                                        <?php if ($changes) { ?><pre><code class="diff"><?= $changes ?></code></pre><?php } ?>
                                    <?php } ?>
                            <?php } else { ?>
                                keine Änderungen vorhanden
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?= LazyLoader::loadModuleReadme($module, '#readme .markdown', 'keine README.md vorhanden'); ?>
        <?= LazyLoader::loadModuleInstallation($module, '#v-pills-install .markdown', 'keine manuelle Installation notwendig'); ?>
        <?= LazyLoader::loadModuleUsage($module, '#v-pills-usage .markdown', 'keine Bedienungsanleitung vorhanden'); ?>
        <?= LazyLoader::loadModuleChangelog($module, '#v-pills-changes .markdown', 'kein Änderungsprotokoll vorhanden'); ?>

        <?php include 'Footer.tmpl.php' ?>
    </body>
</html>
