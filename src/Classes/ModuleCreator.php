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

use RobinTheHood\ModifiedModuleLoaderClient\App;
use RobinTheHood\ModifiedModuleLoaderClient\Loader\RemoteModuleLoader;

class ModuleCreator
{
    public function createModule($vendorPrefix, $vendorName, $moduleName)
    {
        // mycompany/my-first-module
        $archiveName = $vendorName . '/' . $moduleName;

        // mc_my_first_module
        $className = $vendorPrefix . '_' . str_replace('-', '_', $moduleName);

        // mc_my_first_module.php
        $fileName = $className . '.php';

        // MyFistModule
        $moduleNameCamelCase = str_replace('-', '', ucwords($moduleName, '-'));

        // MODULE_MY_FIRST_MODULE
        $moduleConstName = str_replace('-', '_', strtoupper('MODULE_' . $vendorPrefix . '_' . $moduleName));

        $this->createFolders($archiveName, $fileName, $vendorName, $moduleNameCamelCase);
        $this->createModuleInfoJsonFile($vendorName, $moduleName);
        $this->createSystemModuleFile($archiveName, $fileName, $className, $moduleConstName);
        $this->createSystemModuleLanguageDeFile($archiveName, $fileName, $moduleConstName, $vendorName);
        $this->createSystemModuleLanguageEnFile($archiveName, $fileName, $moduleConstName, $vendorName);
    }

    public function createFolders($archiveName, $fileName, $vendorName, $moduleNameCamelCase)
    {
        @mkdir(App::getModulesRoot() . '/' . $archiveName, 0777, true);
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/docs');

        file_put_contents(App::getModulesRoot() . '/' . $archiveName . '/docs/install.md', '');
        file_put_contents(App::getModulesRoot() . '/' . $archiveName . '/docs/usage.md', '');
        file_put_contents(App::getModulesRoot() . '/' . $archiveName . '/changelog.md', '');

        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/src');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/src/admin');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/src/admin/includes');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/src/admin/includes/extra');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/src/admin/includes/modules');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/src/admin/includes/modules/system');
        file_put_contents(App::getModulesRoot() . '/' . $archiveName . '/src/admin/includes/modules/system/' . $fileName, '');

        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/src/includes');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/src/includes/extra');

        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/src/lang');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/src/lang/german');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/src/lang/german/modules');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/src/lang/german/modules/system');
        file_put_contents(App::getModulesRoot() . '/' . $archiveName . '/src/lang/german/modules/system/' . $fileName, '');

        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/src/lang/english');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/src/lang/english/modules');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/src/lang/english/modules/system');
        file_put_contents(App::getModulesRoot() . '/' . $archiveName . '/src/lang/english/modules/system/' . $fileName, '');

        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/src-mmlc/vendor-no-composer');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/src-mmlc/Classes');
    }

    private function getLatestVersion(string $archiveName): string
    {
        $remoteModuleLoader = RemoteModuleLoader::create();
        $module = $remoteModuleLoader->loadLatestVersionByArchiveName($archiveName);

        /**
         * Usually the module does not exist yet, so `$module` is `null`.
         */
        if (null === $module) {
            return '0.1.0';
        }

        return $module->getVersion();
    }

    public function createModuleInfoJsonFile($vendorName, $moduleName)
    {
        $archiveName = $vendorName . '/' . $moduleName;

        $info = [
            'name' => $moduleName,
            'archiveName' => $archiveName,
            'sourceDir' => 'src',
            'version' => 'auto',

            'shortDescription' => 'Kurzbeschreibung für ' . $moduleName,
            'description' => 'Beschreibung für ' . $moduleName,
            'installation' => 'Installationsanleitung für ' . $moduleName,

            'developer' => '',
            'developerWebsite' => 'https://...',
            'website' => 'https://...',

            'category' => '',
            'price' => '',

            'require' => [
                'composer/autoload' => '^' . $this->getLatestVersion('RobinTheHood/modified-composer-autoload'),
                'robinthehood/modified-std-module' => '^' . $this->getLatestVersion('RobinTheHood/modified-std-module'),
            ],

            'modifiedCompatibility' => [
                '2.0.4.2'
            ],

            "mmlc" => [
                "version" => "^1.21.0"
            ],

            "php" => [
                "version" => "^7.4 || ^8.0",
                "ext" => []
            ]
        ];

        $json = json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        \file_put_contents(App::getModulesRoot() . '/' . $archiveName . '/moduleinfo.json', $json);
    }


    public function createSystemModuleFile($archiveName, $fileName, $className, $moduleConstName)
    {
        $content = '<?php

defined(\'_VALID_XTC\') or die(\'Direct Access to this location is not allowed.\');

use RobinTheHood\ModifiedStdModule\Classes\StdModule;
require_once DIR_FS_DOCUMENT_ROOT . \'/vendor-no-composer/autoload.php\';

class ' . $className . ' extends StdModule
{
    public function __construct()
    {
        $this->init(\'' . $moduleConstName . '\');
    }

    public function display()
    {
        return $this->displaySaveButton();
    }

    public function install()
    {
        parent::install();
    }

    public function remove()
    {
        parent::remove();
    }
}
';

        \file_put_contents(App::getModulesRoot() . '/' . $archiveName . '/src/admin/includes/modules/system/' . $fileName, $content);
    }

    public function createSystemModuleLanguageDeFile($archiveName, $fileName, $moduleConstName, $vendorName)
    {
        $content = '<?php

define(\'' . $moduleConstName . '_TITLE\', \'' . $archiveName . ' © by <a href="#" target="_blank" style="color: #e67e22; font-weight: bold;">' . $vendorName . '</a>\');
define(\'' . $moduleConstName . '_LONG_DESCRIPTION\', \'Lange Beschreibung für ' . $archiveName . '\');
define(\'' . $moduleConstName . '_STATUS_TITLE\', \'' . $archiveName . ' Modul aktivieren?\');
define(\'' . $moduleConstName . '_STATUS_DESC\', \'\');
';

        \file_put_contents(App::getModulesRoot() . '/' . $archiveName . '/src/lang/german/modules/system/' . $fileName, $content);
    }

    public function createSystemModuleLanguageEnFile($archiveName, $fileName, $moduleConstName, $vendorName)
    {
        $content = '<?php

define(\'' . $moduleConstName . '_TITLE\', \'' . $archiveName . ' © by <a href="#" target="_blank" style="color: #e67e22; font-weight: bold;">' . $vendorName . '</a>\');
define(\'' . $moduleConstName . '_LONG_DESCRIPTION\', \'Long description for ' . $archiveName . '\');
define(\'' . $moduleConstName . '_STATUS_TITLE\', \'' . $archiveName . ' Modul active?\');
define(\'' . $moduleConstName . '_STATUS_DESC\', \'\');
';

        \file_put_contents(App::getModulesRoot() . '/' . $archiveName . '/src/lang/english/modules/system/' . $fileName, $content);
    }
}
