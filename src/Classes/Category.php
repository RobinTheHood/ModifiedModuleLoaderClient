<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient;


class Category
{
    public static function groupByCategory($modules)
    {
        $groupedModules = [];
        foreach($modules as $module) {
            $category = $module->getCategory();
            $groupedModules[$category][] = $module;
        }

        if (isset($groupedModules[''])) {
            $groupedModules['nocategory'] = $groupedModules[''];
            unset($groupedModules['']);
        }

        if (isset($groupedModules['library'])) {
            $temp = $groupedModules['library'];
            unset($groupedModules['library']);
            $groupedModules['library'] = $temp;
        }

        return $groupedModules;
    }

    public static function getCategoryName($category)
    {
        $allCategories = array(
          'import/export' => 'Import/Export',
          'language' => 'Sprachpaket',
          'persistance' => 'Datenbank Module',
          'productivity' => 'Produktivität',
          'promotion/marketing' => 'Promotion & Marketing',
          'productinfos' => 'Zusatzinformationen & Produkt-Tabs',
          'shipping' => 'Versand Module',
          'library' => 'Programmcode Bibliotheken',
          'nocategory' => 'Sonstige Module',
        );

        return isset( $allCategories[$category] ) ? $allCategories[$category] : $category;
    }
}
