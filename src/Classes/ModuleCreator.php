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
        $this->createModuleInfoJsonFile($archiveName, $moduleName);
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

        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/new_files');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/new_files/admin');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/new_files/admin/includes');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/new_files/admin/includes/extra');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/new_files/admin/includes/modules');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/new_files/admin/includes/modules/system');
        file_put_contents(App::getModulesRoot() . '/' . $archiveName . '/new_files/admin/includes/modules/system/' . $fileName, '');

        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/new_files/includes');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/new_files/includes/extra');

        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/new_files/lang');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/new_files/lang/german');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/new_files/lang/german/modules');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/new_files/lang/german/modules/system');
        file_put_contents(App::getModulesRoot() . '/' . $archiveName . '/new_files/lang/german/modules/system/' . $fileName, '');

        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/new_files/lang/english');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/new_files/lang/english/modules');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/new_files/lang/english/modules/system');
        file_put_contents(App::getModulesRoot() . '/' . $archiveName . '/new_files/lang/english/modules/system/' . $fileName, '');

        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/new_files/vendor-no-composer');
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/new_files/vendor-no-composer/' . $vendorName);
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/new_files/vendor-no-composer/' . $vendorName . '/' . $moduleNameCamelCase);
        @mkdir(App::getModulesRoot() . '/' . $archiveName . '/new_files/vendor-no-composer/' . $vendorName . '/' . $moduleNameCamelCase . '/Classes');
    }

    public function createModuleInfoJsonFile($archiveName, $moduleName)
    {
        $info = [
            'name' => $moduleName,
            'archiveName' => $archiveName,
            'sourceDir' => 'new_files',
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
                'composer/autoload' => '^1.1.0',
                'robinthehood/modified-std-module' => '^0.1.0'
            ],

            'modifiedCompatibility' => [
                '2.0.4.2'
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

        \file_put_contents(App::getModulesRoot() . '/' . $archiveName . '/new_files/admin/includes/modules/system/' . $fileName, $content);
    }

    public function createSystemModuleLanguageDeFile($archiveName, $fileName, $moduleConstName, $vendorName)
    {
        $content = '<?php

define(\'' . $moduleConstName . '_TITLE\', \'' . $archiveName . ' © by <a href="#" target="_blank" style="color: #e67e22; font-weight: bold;">' . $vendorName . '</a>\');
define(\'' . $moduleConstName . '_LONG_DESCRIPTION\', \'Lange Beschreibung für ' . $archiveName . '\');
define(\'' . $moduleConstName . '_STATUS_TITLE\', \'' . $archiveName . ' Modul aktivieren?\');
define(\'' . $moduleConstName . '_STATUS_DESC\', \'\');
';

        \file_put_contents(App::getModulesRoot() . '/' . $archiveName . '/new_files/lang/german/modules/system/' . $fileName, $content);
    }

    public function createSystemModuleLanguageEnFile($archiveName, $fileName, $moduleConstName, $vendorName)
    {
        $content = '<?php

define(\'' . $moduleConstName . '_TITLE\', \'' . $archiveName . ' © by <a href="#" target="_blank" style="color: #e67e22; font-weight: bold;">' . $vendorName . '</a>\');
define(\'' . $moduleConstName . '_LONG_DESCRIPTION\', \'Long description for ' . $archiveName . '\');
define(\'' . $moduleConstName . '_STATUS_TITLE\', \'' . $archiveName . ' Modul active?\');
define(\'' . $moduleConstName . '_STATUS_DESC\', \'\');
';

        \file_put_contents(App::getModulesRoot() . '/' . $archiveName . '/new_files/lang/english/modules/system/' . $fileName, $content);
    }
}
