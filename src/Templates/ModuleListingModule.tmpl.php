<?php

defined('LOADED_FROM_INDEX') && LOADED_FROM_INDEX ?? die('Access denied.');

use RobinTheHood\ModifiedModuleLoaderClient\ViewModels\ModuleViewModel;

$moduleView = new ModuleViewModel($module);

$dataTags = $module->getName() . ' ' . $module->getArchiveName() . ' ' . str_replace(',', ' ', $module->getTags());

if ($module->isCompatible()) {
    $compatibility = 'kompatibel';
    $tooltip = 'Dieses Modul wurde getestet und funktioniert mit deiner Version von modified.';
} else {
    $compatibility = 'inkompatibel';
    $tooltip = 'Dieses Modul wurde noch nicht mit deiner Version von modified getestet.';
}

$modulePrice = $module->isInstalled() ? 'installiert' : $moduleView->getPriceFormated();
$moduleLink = '?action=moduleInfo&archiveName=' . $module->getArchiveName() . '&version=' . $module->getVersion();
$moduleAuthor = $module->getAuthor();
$moduleAuthorWebsite = $module->getAuthorWebsite();
?>

<div class="card module-serach-box <?= $module->isCompatible() ? 'compatible' : 'incompatible'; ?>" data-tags="<?= $dataTags ?>">
    <a href="<?= $moduleLink ?>">
        <img src="<?= $module->getIconUri(); ?>" class="card-img-top" alt="<?= $module->getName(); ?>">
    </a>

    <div class="card-body">
        <h5 class="card-title">
            <?= $module->getName(); ?>
            <span class="card-version"><?= $module->getVersion(); ?></span>
            <div class="card-author">
                <?php if ($moduleAuthorWebsite) { ?>
                    <a href="<?= $module->getAuthorWebsite(); ?>"><?= $moduleAuthor ?></a>
                <?php } else { ?>
                    <?= $moduleAuthor ?>
                <?php } ?>
            </div>
        </h5>

        <div class="module-price">
            <?= $modulePrice ?>
        </div>

        <div class="card-compatibility" data-tooltip="<?= $tooltip; ?>">
            <?= $compatibility; ?>
        </div>

        <p class="card-text"><?= strip_tags($module->getShortDescription()); ?></p>

        <a href="<?= $moduleLink ?>" class="btn <?= $module->isCompatible() ? 'btn-primary' : 'btn-secondary'; ?>">Details</a>
    </div>
</div>
