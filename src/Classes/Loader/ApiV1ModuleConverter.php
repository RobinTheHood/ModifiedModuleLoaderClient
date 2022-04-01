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

namespace RobinTheHood\ModifiedModuleLoaderClient\Loader;

use RobinTheHood\ModifiedModuleLoaderClient\Module;
use RobinTheHood\ModifiedModuleLoaderClient\ModuleFactory;

class ApiV1ModuleConverter
{
    /**
     * @return Module[]
     */
    public function convertToModules($result): array
    {

        $content = $result['content'] ?? [];
        if (!$content) {
            return [];
        }

        $modules = [];
        foreach ($content as $moduleArray) {
            try {
                $module = ModuleFactory::createFromArray($moduleArray);
                $modules[] = $module;
            } catch (\RuntimeException $e) {
                // do nothing
            }
        }

        return $modules;
    }
}
