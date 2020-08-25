<?php if (!defined('LOADED_FROM_INDEX') || LOADED_FROM_INDEX != 'true') { die('Access denied.'); }?>

<?php use RobinTheHood\ModifiedModuleLoaderClient\ModuleStatus; ?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <?php include 'Head.tmpl.php' ?>
    </head>

    <body>
        <?php include 'Navi.tmpl.php' ?>

        <div class="content module-listing">

            <div>
                <?php echo RobinTheHood\ModifiedModuleLoaderClient\Notification::renderFlashMessages() ?>

                <?php if (isset($_GET['filterModules']) && $_GET['filterModules'] == 'loaded') { ?>
                    <h1>Geladene Module</h1>
                <?php } elseif (isset($_GET['filterModules']) && $_GET['filterModules'] == 'installed') { ?>
                    <h1>Installierte Module</h1>
                <?php } elseif (isset($_GET['filterModules']) && $_GET['filterModules'] == 'updatable') { ?>
                    <h1>Aktualisierbare Module</h1>
                <?php } elseif (isset($_GET['filterModules']) && $_GET['filterModules'] == 'changed') { ?>
                    <h1>Geänderte Module</h1>
                <?php } elseif (isset($_GET['filterModules']) && $_GET['filterModules'] == 'notloaded') { ?>
                    <h1>Nicht geladene Module</h1>
                <?php } else { ?>
                    <h1>Alle Module</h1>
                <?php } ?>

                <div class="search">
                    <h2>Suche</h2>
                    <input id="filterModuleSearch" type="text" value="" placeholder="Suche Modul- oder Archivname" onkeyup="filterModule();">

                    <h3>Optionen</h3>
                    <fieldset>
                        <input id="filterModuleOptionFree" type="checkbox" onchange="filterModule();">
                        <label for="filterModuleOptionFree">Nur kostenlose Module suchen</label>
                    </fieldset>
                </div>

                <div class="modules">
                    <?php foreach($groupedModules as $category => $modules) { ?>
                        <h2><?php echo RobinTheHood\ModifiedModuleLoaderClient\Category::getCategoryName($category); ?></h2>

                        <div class="category">
                            <?php foreach($modules as $module) { ?>
                                <?php if ($module->getVisibility() == 'hidden') { continue; } ?>

                                <div
                                class="card module-serach-box <?php echo $module->isCompatible() ? 'compatible' : 'incompatible'; ?>"
                                data-tags="<?php echo $module->getName(); ?> <?php echo $module->getArchiveName()?> <?php echo str_replace(',', ' ', $module->getTags())?>"
                                data-price="<?php echo $module->getPrice(); ?>">
                                    <a href="?action=moduleInfo&archiveName=<?php echo $module->getArchiveName()?>&version=<?php echo $module->getVersion() ?>">
                                        <img src="<?php echo $module->getIconUri(); ?>" class="card-img-top" alt="<?php echo $module->getName(); ?>">
                                    </a>

                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <?php echo $module->getName(); ?>
                                            <span class="card-version"><?php echo $module->getVersion(); ?></span>
                                        </h5>

                                        <div class="module-price">
                                            <?php
                                                if ($module->isInstalled()) {
                                                    echo 'installiert';
                                                } else {
                                                    echo $module->getPriceFormated();
                                                }
                                            ?>
                                        </div>

                                        <?php
                                            $compatibility = $module->isCompatible() ? 'kompatibel' : 'inkompatibel';
                                            $tooltip = $module->isCompatible() ? 'Dieses Modul wurde getestet und funktioniert mit deiner Version von modified.' : 'Dieses Modul wurde noch nicht mit deiner Version von modified getestet.';
                                        ?>
                                        <div class="card-compatibility" data-tooltip="<?php echo $tooltip; ?>">
                                            <?php echo $compatibility; ?>
                                        </div>

                                        <p class="card-text"><?php echo $module->getShortDescription(); ?></p>

                                        <a href="?action=moduleInfo&archiveName=<?php echo $module->getArchiveName()?>&version=<?php echo $module->getVersion() ?>" class="btn <?php echo $module->isCompatible() ? 'btn-primary' : 'btn-secondary'; ?>">Details</a>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php include 'Footer.tmpl.php' ?>
    </body>
</html>
