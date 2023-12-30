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

namespace RobinTheHood\ModifiedModuleLoaderClient\ModuleManager;

use RobinTheHood\ModifiedModuleLoaderClient\Module;

class AutoloadEntry
{
    public const TYPE_PSR_4 = 1;

    /**
     * @var int $type
     */
    public $type;

    /**
     * @var ?Module $module
     */
    public $module;

    /**
     * @var string $namespace
     */
    public $namespace;

    /**
     * @var string $path
     */
    public $path;

    /**
     * @var string $realPath
     */
    public $realPath;

    /**
     * AutoloadEntry constructor.
     *
     * @param int $type
     * @param ?Module $module
     * @param string $namespace
     * @param string $path
     * @param string $realPath
     */
    public function __construct(int $type, ?Module $module, string $namespace, string $path, string $realPath)
    {
        //TODO: Kontrolloeren ob es sich bei $namespace, path und $realPath um valide Werte handelt.
        $this->type = $type;
        $this->module = $module;
        $this->namespace = $namespace;
        $this->path = $path;
        $this->realPath = $realPath;
    }

    public static function createFromModule(
        Module $module,
        string $namespace,
        string $path,
        int $type = self::TYPE_PSR_4
    ): AutoloadEntry {
        $realPath = str_replace(
            $module->getSourceMmlcDir(),
            'vendor-mmlc/' . $module->getArchiveName(),
            $path
        );
        return new AutoloadEntry($type, $module, $namespace, $path, $realPath);
    }
}
