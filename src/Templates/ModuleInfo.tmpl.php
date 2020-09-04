<?php if (!defined('LOADED_FROM_INDEX') || LOADED_FROM_INDEX != 'true') { die('Access denied.'); }?>

<?php use RobinTheHood\ModifiedModuleLoaderClient\ModuleStatus; ?>
<?php use RobinTheHood\ModifiedModuleLoaderClient\LazyLoader; ?>
<?php use RobinTheHood\ModifiedModuleLoaderClient\ShopInfo; ?>
<?php use RobinTheHood\ModifiedModuleLoaderClient\Config; ?>

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
                <?php echo RobinTheHood\ModifiedModuleLoaderClient\Notification::renderFlashMessages() ?>

                <div class="row">
                    <div class="col">
                        <div class="module-title">
                            <img src="<?php echo $module->getIconUri(); ?>">

                            <h1><?php echo $module->getName() ?></h1>
                        </div>

                        <?php if ($module->getImageUris()) { ?>
                            <div class="module-previews">
                            <?php foreach($module->getImageUris() as $image) { ?>
                                <div class="preview">
                                    <a href="<?php echo $image ?>" data-lightbox="show-1" data-title="<?php echo $module->getName() ?>">
                                        <img src="<?php echo $image ?>">
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
                                Dieses Modul wurde noch nicht mit deiner Version von modified getestet. Du hast modifed <strong><?php echo ShopInfo::getModifiedVersion()?></strong> installiert.
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="moduleinfo-buttons">
                <?php if (ModuleStatus::isUpdatable($module) && !ModuleStatus::isRepairable($module)) { ?>
                    <a class="button button-success" href="?action=update&archiveName=<?php echo $module->getArchiveName() ?>&version=<?php echo $module->getVersion() ?>&ref=moduleInfo">Update installieren</a>
                <?php } ?>

                <?php if (ModuleStatus::isRepairable($module)) { ?>
                    <a class="button button-danger" onclick="return confirm('Möchtest du deine Änderungen wirklich rückgängig machen?');" href="?action=install&archiveName=<?php echo $module->getArchiveName()?>&version=<?php echo $module->getVersion()?>&ref=moduleInfo">
                        <?php if (Config::getInstallMode() != 'link') {?>
                            Änderungen verwerfen
                        <?php } else { ?>
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
                    <a class="button button-default" href="?action=loadAndInstall&archiveName=<?php echo $module->getArchiveName()?>&version=<?php echo $module->getVersion()?>&ref=moduleInfo">Download & Install</a>

                <?php } elseif (ModuleStatus::isUncompatibleLoadebale($module)) { ?>
                    <a class="button button-default" href="?action=loadRemoteModule&archiveName=<?php echo $module->getArchiveName()?>&version=<?php echo $module->getVersion() ?>&ref=moduleInfo">Download (inkompatible Version)</a>

                <?php } elseif (ModuleStatus::isUninstallable($module) && !ModuleStatus::isRepairable($module)) { ?>
                    <a class="button button-danger" href="?action=uninstall&archiveName=<?php echo $module->getArchiveName()?>&version=<?php echo $module->getVersion() ?>&ref=moduleInfo">Deinstallieren</a>

                <?php } elseif (ModuleStatus::isCompatibleInstallable($module)) { ?>
                    <a class="button button-success" href="?action=install&archiveName=<?php echo $module->getArchiveName() ?>&version=<?php echo $module->getVersion() ?>&ref=moduleInfo">Installieren</a>

                <?php } elseif (ModuleStatus::isUncompatibleInstallable($module)) { ?>
                    <a class="button button-success" href="?action=install&archiveName=<?php echo $module->getArchiveName() ?>&version=<?php echo $module->getVersion() ?>&ref=moduleInfo">Installieren (inkompatible Version)</a>

                <?php } elseif ($installedModule = $module->getInstalledVersion()) { ?>
                    <?php if ($installedModule->getVersion() != $module->getVersion()) { ?>
                        <a class="button button-default" href="?action=moduleInfo&archiveName=<?php echo $installedModule->getArchiveName() ?>&version=<?php echo $installedModule->getVersion() ?>&ref=moduleInfo">Zur installierten Version</a>
                    <?php } ?>
                <?php } ?>

                <?php if (!$module->isRemote() && $module->isLoaded() && !$module->isInstalled()) { ?>
                    <a class="button button-danger" onclick="return confirm('Möchtest du das Modul wirklich entfernen?');" href="?action=unloadLocalModule&archiveName=<?php echo $module->getArchiveName()?>&version=<?php echo $module->getVersion() ?>&ref=moduleInfo">Modul löschen</a>
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
                                            <td><?php echo $module->getVersion(); ?></td>
                                        </tr>

                                        <tr>
                                            <td>Preis</td>
                                            <td><?php echo $module->getPriceFormated(); ?></td>
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

                                                        <span class="<?php echo implode(' ', $badgeClasses); ?>"><?php echo $badgeInnerHTML; ?></span>
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
                                                    <a target="_blank" href="<?php echo $module->getDeveloperWebsite() ?>"><?php echo $module->getDeveloper() ?></a>
                                                <?php } elseif ($module->getDeveloper()) { ?>
                                                    <?php echo $module->getDeveloper() ?>
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
                                        <?php echo $module->getDescription() ?>
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
                        <?php echo LazyLoader::loadModuleInstallation($module, '#v-pills-install .markdown', 'keine manuelle Installation notwendig'); ?>

                        <div class="tab-pane fade" id="v-pills-usage" role="tabpanel" aria-labelledby="v-pills-usage-tab">
                            <div class="markdown">
                                Wird geladen. Bitte warten...
                            </div>
                        </div>
                        <?php echo LazyLoader::loadModuleUsage($module, '#v-pills-usage .markdown', 'keine Bedienungsanleitung vorhanden'); ?>

                        <div class="tab-pane fade" id="v-pills-changes" role="tabpanel" aria-labelledby="v-pills-changes-tab">
                            <div class="markdown changelog">
                                Wird geladen. Bitte warten...
                            </div>
                        </div>
                        <?php echo LazyLoader::loadModuleChangelog($module, '#v-pills-changes .markdown', 'kein Änderungsprotokoll vorhanden'); ?>

                        <div class="tab-pane fade" id="v-pills-details" role="tabpanel" aria-labelledby="v-pills-details-tab">
                            <div class="infos">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <td>Archivname</td>
                                            <td><?php echo $module->getArchiveName(); ?></td>
                                        </tr>

                                        <tr>
                                            <td>Version</td>
                                            <td><?php echo $module->getVersion(); ?></td>
                                        </tr>

                                        <tr>
                                            <td>Kompatibel mit Modified</td>
                                            <td>
                                                <?php if ($module->getModifiedCompatibility()) { ?>
                                                    <?php foreach ($module->getModifiedCompatibility() as $version) { ?>
                                                        <span class="badge badge-secondary"><?php echo $version; ?></span>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    unbekannt
                                                <?php } ?>
                                            </td>
                                        </tr>

                                        <?php if($module->getTags()) { ?>
                                            <tr>
                                                <td>Tags</td>
                                                <td>
                                                    <?php foreach (explode(',', $module->getTags()) as $tag) { ?>
                                                        <span class="badge badge-secondary"><?php echo trim($tag); ?></span>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php } ?>

                                        <tr>
                                            <td>Entwickler</td>
                                            <td>
                                                <?php if ($module->getDeveloper() && $module->getDeveloperWebsite()) { ?>
                                                    <a target="_blank" href="<?php echo $module->getDeveloperWebsite() ?>"><?php echo $module->getDeveloper() ?></a>
                                                <?php } elseif ($module->getDeveloper()) { ?>
                                                    <?php echo $module->getDeveloper() ?>
                                                <?php } else { ?>
                                                    unbekannter Entwickler
                                                <?php } ?>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>Alle Versionen</td>
                                            <td>
                                                <?php foreach ($module->getVersions() as $moduleVersion) {?>
                                                    <a href="?action=moduleInfo&archiveName=<?php echo $moduleVersion->getArchiveName() ?>&version=<?php echo $moduleVersion->getVersion()?>"><?php echo $moduleVersion->getVersion(); ?></a>
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
                                                        <a href="?action=moduleInfo&archiveName=<?php echo $archiveName?>"><?php echo $archiveName?></a><span>: <?php echo $version ?></span><br>
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
                                                        <a href="?action=moduleInfo&archiveName=<?php echo $usedBy->getArchiveName()?>&version=<?php echo $usedBy->getVersion() ?>"><?php echo $usedBy->getArchiveName()?></a><span>: <?php echo $usedBy->getVersion() ?></span><br>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    wird von keinem Modul verwendet
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="v-pills-files" role="tabpanel" aria-labelledby="v-pills-files-tab">
                            <h3>Geänderte Dateien</h3>

                            <?php if ($module->isInstalled() && $module->isChanged()) { ?>

                                    <?php foreach ($module->getChancedFiles() as $file => $mode) { ?>
                                        <?php $changes = htmlentities(RobinTheHood\ModifiedModuleLoaderClient\ModuleHasher::getFileChanges($module, $file, $mode)); ?>

                                        <div><?php echo $file ?><span>: <?php echo $mode ?></span></div>
                                        <?php if ($changes) {?><pre><code class="diff"><?php echo $changes ?></code></pre><?php }?>
                                    <?php } ?>
                            <?php } else { ?>
                                keine Änderungen vorhanden
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'Footer.tmpl.php' ?>
    </body>
</html>
