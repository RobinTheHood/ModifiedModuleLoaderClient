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

class Category
{
    private const DEFAULT_CATEGORY = 'nocategory';

    private const CATEGORIES = [
        'import/export' => 'Import/Export',
        'language' => 'Sprachpaket',
        'persistance' => 'Datenbank Module',
        'productivity' => 'Produktivität',
        'promotion/marketing' => 'Promotion & Marketing',
        'productinfos' => 'Zusatzinformationen & Produkt-Tabs',
        'shipping' => 'Versand Module',
        'payment' => 'Zahlungs Module',
        'library' => 'Programmcode Bibliotheken',
        'nocategory' => 'Sonstige Module',
    ];

    /**
     * @param Module[] $modules
     * @return array<string, Module[]> Returns a list of modules grouped by category.
     */
    public static function groupByCategory(array $modules): array
    {
        $groupedModules = [];
        foreach ($modules as $module) {
            $category = $module->getCategory();
            $category = self::getCategory($category);
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

    public static function getCategory(string $category): string
    {
        if (isset(self::CATEGORIES[$category])) {
            return $category;
        }
        return self::DEFAULT_CATEGORY;
    }

    public static function getCategoryName(string $category): string
    {
        return self::CATEGORIES[$category] ?? self::CATEGORIES[self::DEFAULT_CATEGORY];
    }
}
