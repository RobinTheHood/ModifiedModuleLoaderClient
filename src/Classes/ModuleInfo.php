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

use RobinTheHood\ModifiedModuleLoaderClient\Helpers\ArrayHelper;

class ModuleInfo
{
    protected $name;
    protected $archiveName;
    protected $sourceDir;
    protected $version;
    protected $shortDescription;
    protected $description;
    protected $developer;
    protected $developerWebsite;
    protected $website;
    protected $require;
    protected $category;
    protected $type;
    protected $modifiedCompatibility;
    protected $installation;
    protected $visibility;
    protected $price;

    public function getName()
    {
        return $this->name;
    }

    public function getArchiveName()
    {
        return $this->archiveName;
    }

    public function getSourceDir()
    {
        return $this->sourceDir;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getDeveloper()
    {
        return $this->developer;
    }

    public function getDeveloperWebsite()
    {
        return $this->developerWebsite;
    }

    public function getWebsite()
    {
        return $this->website;
    }

    public function getRequire()
    {
        return $this->require;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getModifiedCompatibility()
    {
        return $this->modifiedCompatibility;
    }

    public function getInstallation()
    {
        return $this->installation;
    }

    public function getVisibility()
    {
        return $this->visibility;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getAutoload()
    {
        return $this->autoload;
    }

    public function loadFromJson($path)
    {
        if (!file_exists($path)) {
            return false;
        }

        $json = file_get_contents($path);
        $array = json_decode($json, true);

        if (!$array) {
            return false;
        }

        return ModuleInfo::loadFromArray($array);
    }

    public function loadFromArray(Array $array)
    {
        $this->name = ArrayHelper::getIfSet($array, 'name');
        $this->archiveName = ArrayHelper::getIfSet($array, 'archiveName');
        $this->sourceDir = ArrayHelper::getIfSet($array, 'sourceDir', 'src');
        $this->version = ArrayHelper::getIfSet($array, 'version');
        $this->shortDescription = ArrayHelper::getIfSet($array, 'shortDescription');
        $this->description = ArrayHelper::getIfSet($array, 'description');
        $this->developer = ArrayHelper::getIfSet($array, 'developer');
        $this->developerWebsite = ArrayHelper::getIfSet($array, 'developerWebsite');
        $this->website = ArrayHelper::getIfSet($array, 'website');
        $this->require = ArrayHelper::getIfSet($array, 'require', []);
        $this->category = ArrayHelper::getIfSet($array, 'category');
        $this->type = ArrayHelper::getIfSet($array, 'type');
        $this->modifiedCompatibility = ArrayHelper::getIfSet($array, 'modifiedCompatibility', []);
        $this->installation = ArrayHelper::getIfSet($array, 'installation');
        $this->visibility = ArrayHelper::getIfSet($array, 'visibility');
        $this->price = ArrayHelper::getIfSet($array, 'price');
        $this->autoload = ArrayHelper::getIfSet($array, 'autoload');

        return true;
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'archiveName' => $this->archiveName,
            'sourceDir' => $this->sourceDir,
            'version' => $this->version,
            'shortDescription' => $this->shortDescription,
            'description' => $this->description,
            'developer' => $this->developer,
            'developerWebsite' => $this->developerWebsite,
            'website' => $this->website,
            'require' => $this->require,
            'category' => $this->category,
            'type' => $this->type,
            'modifiedCompatibility' => $this->modifiedCompatibility,
            'installation' => $this->installation,
            'visibility' => $this->visibility,
            'price' => $this->price,
            'autoload' => $this->autoload
        ];
    }
}
