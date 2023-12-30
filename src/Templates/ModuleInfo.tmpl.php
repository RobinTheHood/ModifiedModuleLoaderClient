<?php

/**
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */

defined('LOADED_FROM_INDEX') && LOADED_FROM_INDEX ?? die('Access denied.');

use RobinTheHood\ModifiedModuleLoaderClient\LazyLoader;
use RobinTheHood\ModifiedModuleLoaderClient\ShopInfo;
use RobinTheHood\ModifiedModuleLoaderClient\Config;
use RobinTheHood\ModifiedModuleLoaderClient\FileHasher\ChangedEntry;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleChangeManager;
use RobinTheHood\ModifiedModuleLoaderClient\ViewModels\NotificationViewModel;
use RobinTheHood\ModifiedModuleLoaderClient\ViewModels\ModuleViewModel;

$moduleView = new ModuleViewModel($module);
$notificationView = new NotificationViewModel();

?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <?php include 'Head.tmpl.php' ?>
        <link rel="stylesheet" href="src/Templates/Styles/highlight-github.css">
        <link rel="stylesheet" href="src/Templates/Styles/github-markdown-css.css">
        <script src="src/Templates/Scripts/highlight.min.js"></script>
        <script src="src/Templates/Scripts/language-smarty.js"></script>
        <script>
            hljs.configure({
                languages: ['smarty', 'php', 'js'],
                ignoreUnescapedHTML: true
            });
            hljs.registerLanguage('smarty', smarty);
        </script>
    </head>

    <body>
        <?php include 'Navi.tmpl.php' ?>

        <div class="block">
            <div class="content">
                <?= $notificationView->renderFlashMessages() ?>

                <?php if ($moduleView->isRepairable()) { ?>
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle fa-fw"></i>
                        Einige Dateien befinden sich nicht mehr im Originalzustand. Möglicherweise hast du an
                        diesen Anpassungen vorgenommen. <strong>Deinstallation</strong> und
                        <strong>Update</strong> stehen dir nur bei unveränderten Modulen zur Verfügung, damit
                        deine Arbeit nicht verloren geht.
                        <a href="#v-pills-tabContent" onclick="$('#v-pills-files-tab').tab('show');">Alle Änderungen ansehen</a>.
                    </div>
                <?php } ?>

                <?php if (!$moduleView->isLoadable()) { ?>
                    <div class="alert alert-primary" role="alert">
                        <i class="fas fa-info-circle fa-fw"></i>
                        Um dieses Modul zu verwenden, nimm bitte Kontakt zum Entwickler auf. Der Entwickler kann
                        dir das Modul (z. B. nach einem Kauf) freischalten.
                    </div>
                <?php } ?>

                <?php if (!$moduleView->isCompatible()) { ?>
                    <?= $notificationView->renderMultibleFlashMessages($moduleView->getCompatibleStrings()) ?>
                <?php } ?>

                <div class="row">
                    <div class="col">
                        <div class="module-title">
                            <img src="<?= $moduleView->getIconUri(); ?>">

                            <h1><?= $moduleView->getName() ?></h1>
                        </div>

                        <?php if ($moduleView->getImageUris()) { ?>
                            <div class="module-previews">
                            <?php foreach ($moduleView->getImageUris() as $imageUri) { ?>
                                <div class="preview">
                                    <a href="<?= $imageUri ?>" data-lightbox="show-1" data-title="<?= $moduleView->getName() ?>">
                                        <img src="<?= $imageUri ?>">
                                    </a>
                                </div>
                            <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <?php include 'ModuleInfoButtons.tmpl.php'; ?>

            <div class="row">
                <div class="col-3">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active show" id="v-pills-overview-tab" data-toggle="pill" href="#v-pills-overview" role="tab" aria-controls="v-pills-overview" aria-selected="true">Übersicht</a>
                        <a class="nav-link" id="v-pills-install-tab" data-toggle="pill" href="#v-pills-install" role="tab" aria-controls="v-pills-install" aria-selected="false">Installation</a>
                        <a class="nav-link" id="v-pills-usage-tab" data-toggle="pill" href="#v-pills-usage" role="tab" aria-controls="v-pills-usage" aria-selected="false">Bedienung</a>
                        <a class="nav-link" id="v-pills-changes-tab" data-toggle="pill" href="#v-pills-changes" role="tab" aria-controls="v-pills-changes" aria-selected="false">Änderungsprotokoll</a>
                        <a class="nav-link" id="v-pills-details-tab" data-toggle="pill" href="#v-pills-details" role="tab" aria-controls="v-pills-details" aria-selected="false">Details</a>
                        <?php if ($moduleView->isRepairable()) { ?>
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
                                            <td><?= $moduleView->getVersionAndGitBranch(); ?></td>
                                        </tr>

                                        <tr>
                                            <td>Preis</td>
                                            <td><?= $moduleView->getPriceFormated(); ?></td>
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

                            <h2>Beschreibung</h2>
                            <div id="description">
                                <div class="markdown-body" style="padding-bottom: 30px">
                                        Beschreibung wird geladen. Bitte warten...
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="v-pills-install" role="tabpanel" aria-labelledby="v-pills-install-tab">
                            <div class="markdown-body">
                                Wird geladen. Bitte warten...
                            </div>
                        </div>


                        <div class="tab-pane fade" id="v-pills-usage" role="tabpanel" aria-labelledby="v-pills-usage-tab">
                            <div class="markdown-body">
                                Wird geladen. Bitte warten...
                            </div>
                        </div>


                        <div class="tab-pane fade" id="v-pills-changes" role="tabpanel" aria-labelledby="v-pills-changes-tab">
                            <div class="markdown-body changelog">
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
                                            <td><?= $moduleView->getVersionAndGitBranch(); ?></td>
                                        </tr>

                                        <tr>
                                            <td>Datum</td>
                                            <td><?= $moduleView->getDate(); ?></td>
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

                                        <tr>
                                            <td>Kompatibel mit PHP</td>
                                            <td>
                                                <?php if ($module->getPhp()) { ?>
                                                    <?php foreach (explode('||', $module->getPhp()['version'] ?? '') as $version) { ?>
                                                        <span class="badge badge-secondary"><?= trim($version); ?></span>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    unbekannt
                                                <?php } ?>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Kompatibel mit MMLC</td>
                                            <td>
                                                <?php if (true || $module->getMmlc()) { ?>
                                                    <?php foreach (explode('||', $module->getMmlc()['version'] ?? '') as $version) { ?>
                                                        <span class="badge badge-secondary"><?= trim($version); ?></span>
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
                                            <td>Website</td>
                                            <td>
                                                <?php if ($module->getWebsite()) { ?>
                                                    <a target="_blank" href="<?= $module->getWebsite() ?>"><?= $module->getWebsite() ?></a>
                                                <?php } else { ?>
                                                    unbekannte Website
                                                <?php } ?>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Alle Versionen</td>
                                            <td>
                                                <div class="expandable">
                                                    <?php foreach ($module->getVersions() as $moduleVersion) {?>
                                                        <?php $moduleVersionView = new ModuleViewModel($moduleVersion) ?>
                                                        <a href="?action=moduleInfo&archiveName=<?= $moduleVersion->getArchiveName() ?>&version=<?= $moduleVersion->getVersion()?>">
                                                            <?= $moduleVersionView->getVersionAndGitBranch(); ?>
                                                        </a>
                                                        <?php if ($moduleVersion->isInstalled()) { ?>
                                                            <span>installiert</span>
                                                        <?php } elseif ($moduleVersion->isLoaded()) { ?>
                                                            <span>geladen</span>
                                                        <?php } else { ?>
                                                            <span>nicht geladen</span>
                                                        <?php } ?>
                                                        <br>
                                                    <?php } ?>
                                                </div>
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
                                    <div class="markdown-body">
                                        README.md Wird geladen. Bitte warten...
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="v-pills-files" role="tabpanel" aria-labelledby="v-pills-files-tab">
                            <h3>Geänderte Dateien</h3>

                            <?php if ($moduleView->isInstalled() && $moduleView->isChanged()) { ?>
                                    <?php foreach ($module->getChancedFiles()->changedEntries as $changedEntry) { ?>
                                        <?php $changes = htmlentities(ModuleChangeManager::getFileChanges($module, $changedEntry)); ?>
                                        <div>(<?= $changedEntry->hashEntryA->scope ?>) <?= $changedEntry->hashEntryA->file ?><span>: <?= ChangedEntry::typeToString($changedEntry->type) ?></span></div>
                                        <?php if ($changes) { ?>
                                            <pre><code class="diff"><?= $changes ?></code></pre>
                                        <?php } ?>
                                    <?php } ?>
                            <?php } else { ?>
                                keine Änderungen vorhanden
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?= LazyLoader::loadModuleDescription($module, '#description .markdown-body', 'Es ist keine Beschreibung vorhanden.'); ?>
        <?= LazyLoader::loadModuleReadme($module, '#readme .markdown-body', 'Es ist keine README.md vorhanden.'); ?>
        <?= LazyLoader::loadModuleInstallation($module, '#v-pills-install .markdown-body', 'Es ist keine manuelle Installationanleitung vorhanden.'); ?>
        <?= LazyLoader::loadModuleUsage($module, '#v-pills-usage .markdown-body', 'Es ist keine Bedienungsanleitung vorhanden.'); ?>
        <?= LazyLoader::loadModuleChangelog($module->getNewestVersion(), '#v-pills-changes .markdown-body', 'Es ist kein Änderungsprotokoll vorhanden.'); ?>

        <?php include 'Footer.tmpl.php' ?>
    </body>
</html>
