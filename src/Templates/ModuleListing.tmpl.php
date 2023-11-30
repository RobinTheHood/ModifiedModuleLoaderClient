<?php
defined('LOADED_FROM_INDEX') && LOADED_FROM_INDEX ?? die('Access denied.');

use RobinTheHood\ModifiedModuleLoaderClient\Category;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleSorter;
use RobinTheHood\ModifiedModuleLoaderClient\ViewModels\NotificationViewModel;

$notificationView = new NotificationViewModel();
?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <?php include 'Head.tmpl.php' ?>
    </head>

    <body>
        <?php include 'Navi.tmpl.php' ?>

        <div class="content module-listing">

            <div>
                <?= $notificationView->renderFlashMessages() ?>

                <div class="search">
                    <input type="text" value="" placeholder="Suche Modul- oder Archivname"
                    onkeyup="filterModule(this.value)">
                </div>

                <h1><?= $heading ?></h1>

                <div class="modules">
                    <?php foreach ($groupedModules as $category => $modules) { ?>
                        <h2><?= Category::getCategoryName($category); ?></h2>
                        <div class="category">
                            <?php
                            $modules = ModuleSorter::sortByDate($modules);
                            foreach ($modules as $module) {
                                if ($module->getVisibility() == 'hidden') {
                                    continue;
                                }
                                if (!$module->isCompatible()) {
                                    continue;
                                }
                                include 'ModuleListingModule.tmpl.php';
                            }

                            foreach ($modules as $module) {
                                if ($module->getVisibility() == 'hidden') {
                                    continue;
                                }
                                if ($module->isCompatible()) {
                                    continue;
                                }
                                include 'ModuleListingModule.tmpl.php';
                            }
                            ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php include 'Footer.tmpl.php' ?>
    </body>
</html>
