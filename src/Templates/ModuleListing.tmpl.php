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

            <div class="container">
                <?php echo RobinTheHood\ModifiedModuleLoaderClient\Notification::renderFlashMessages() ?>

                <div class="search">
                    <input type="text" value="" placeholder="Suche Modul- oder Archivname" onkeyup="filterModule(this.value)">
                </div>

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

                <div class="modules">
                    <?php foreach($groupedModules as $category => $modules) { ?>
                        <h2><?php echo RobinTheHood\ModifiedModuleLoaderClient\Category::getCategoryName($category); ?></h2>

                        <div class="row">
                            <?php foreach($modules as $module) { ?>
                                <?php if ($module->getVisibility() == 'hidden') { continue; } ?>

                                <div class="col-3 module-serach-box" data-tags="<?php echo $module->getName(); ?> <?php echo $module->getArchiveName()?>">
                                    <div class="module-box" onclick="location.href='?action=moduleInfo&archiveName=<?php echo $module->getArchiveName()?>&version=<?php echo $module->getVersion() ?>'">

                                        <div class="module-badge"></div>

                                        <div class="module-icon">
                                            <img src="<?php echo $module->getIconUri(); ?>">
                                        </div>

                                        <div class="module-title">
                                            <?php echo $module->getName(); ?>
                                        </div>

                                        <div class="module-version">
                                            <?php echo $module->getVersion(); ?>
                                        </div>

                                        <div class="module-shortdescription">
                                            <?php echo $module->getShortDescription(); ?>
                                        </div>

                                        <div class="module-price">
                                            <?php
                                                if ($module->isInstalled()) {
                                                    echo 'Installiert';
                                                } else {
                                                    echo $module->getPriceFormated();
                                                }
                                            ?>
                                        </div>
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
