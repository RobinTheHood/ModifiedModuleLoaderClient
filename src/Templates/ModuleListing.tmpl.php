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
                    <?php if ($filterModulesBy === 'all') { ?>
                        <h2>Neuste und aktualisierte Module</h2>
                        <div class="category">
                            <?php
                            $modules = ModuleSorter::sortByDate($modules);
                            $modules = array_filter($modules, function ($module) {
                                return $module->getCategory() !== 'library';
                            });
                            $modules = array_values($modules);

                            for ($index = 0; $index < min(count($modules), 6); $index++) {
                                $module = $modules[$index];
                                include 'ModuleListingModule.tmpl.php';
                            }
                            ?>
                        </div>
                    <?php } ?>

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
