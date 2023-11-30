<?php

declare(strict_types=1);

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient;

use RobinTheHood\ModifiedModuleLoaderClient\Module;

class ModuleConverter
{
    public static function convertToArray(Module $module): array
    {
        $moduleInfoArray = [
            'name' => $module->getName(),
            'archiveName' => $module->getArchiveName(),
            'sourceDir' => $module->getSourceDir(),
            'sourceMmlcDir' => $module->getSourceMmlcDir(),
            'version' => $module->getVersion(),
            'date' => $module->getDate(),
            'shortDescription' => $module->getShortDescription(),
            'description' => $module->getDescription(),
            'developer' => $module->getDeveloper(),
            'developerWebsite' => $module->getDeveloperWebsite(),
            'website' => $module->getWebsite(),
            'require' => $module->getRequire(),
            'category' => $module->getCategory(),
            'type' => $module->getType(),
            'modifiedCompatibility' => $module->getModifiedCompatibility(),
            'installation' => $module->getInstallation(),
            'visibility' => $module->getVisibility(),
            'price' => $module->getPrice(),
            'autoload' => $module->getAutoload(),
            'tags' => $module->getTags(),
            'php' => $module->getPhp(),
            'mmlc' => $module->getMmlc()
        ];

        $moduleArray = [
            'localRootPath' => $module->getLocalRootPath(),
            'urlRootPath' => $module->getUrlRootPath(),
            'modulePath' => $module->getModulePath(),
            'iconPath' => $module->getIconPath(),
            'imagePaths' => $module->getImagePaths(),
            'docFilePaths' => $module->getDocFilePaths(),
            'changelogPath' => $module->getChangelogPath(),
            'readmePath' => $module->getReadmePath(),
            'srcFilePaths' => $module->getSrcFilePaths(),
            'srcMmlcFilePaths' => $module->getSrcMmlcFilePaths(),
            'isRemote' => $module->isRemote(),
            'isLoadable' => $module->isLoadable()
        ];

        return array_merge($moduleInfoArray, $moduleArray);
    }
}
