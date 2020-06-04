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
        if ($category == 'import/export') {
            return 'Import/Export';

        } elseif ($category == 'persistance') {
            return 'Datenbank Module';

        } elseif ($category == 'productivity') {
            return 'Produktivität';

        } elseif ($category == 'promotion/marketing') {
            return 'Promotion & Marketing';

        } elseif ($category == 'productinfos') {
            return 'Zusatzinformationen & Produkt-Tabs';

        } elseif ($category == 'shipping') {
            return 'Versand Module';

        } elseif ($category == 'library') {
            return 'Programmcode Bibliotheken';

        } elseif ($category == 'nocategory') {
            return 'Sonstige Module';

        } elseif ($category) {
            return $category;
        }

        return 'Sonstige Module';
    }
}