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

/**
 * Die Klasse ModuleInfo repräsentiert die Daten einer moduleinfo.json
 */
class ModuleInfo
{
    /**
     * Name des Moduls in menschen lesbarer Form.
     *
     * @var string
     */
    protected $name;

    /**
     * Der eindeutige archiveName des Moduls. Der archiveName
     * setzt sich aus den vendorName / moduleName zusammen.
     *
     * Beispiel: robinthehood/modified-std-module
     *
     * @var string
     */
    protected $archiveName;
    
    /**
     * Das Verzeichnis, in dem sich die Quellcode Dateien des Moduls befinden,
     * die in den Shop kopiert/verlinkt werden sollen.
     *
     * Beispiel: new_fieles
     *
     * @var string
     */
    protected $sourceDir;
    
    /**
     * Die Version des Moduls. Die Version muss der Sermver konvention folgen.
     * Der Wert darf auch 'auto' sein. In diesem Fall wird versucht, sich die
     * Versionsnummer aus anderen Quellen zu holen wie z. B. aus git Tags.
     *
     * Beispiel: 1.1.0
     *
     * @var string
     */
    protected $version;
    
    /**
     * Eine Kurzbeschreibung des Moduls in menschen lesbarer Form.
     *
     * @var string
     */
    protected $shortDescription;
    
    /**
     * Eine Beschreibung des Moduls in menschen lesbarer Form.
     *
     * @var string
     */
    protected $description;
    
    /**
     * Name des Entwickler / Entwicklerfirma, die das Modul
     * programmiert.
     *
     * @var string
     */
    protected $developer;
    
    /**
     * HIER FEHLT EINE BESCHREIBUNG
     *
     * @var string
     */
    protected $developerWebsite;
    
    /**
     * HIER FEHLT EINE BESCHREIBUNG
     *
     * @var string
     */
    protected $website;
    
    /**
     * Ein Array mit archiveNamen mit Version, von denen das Modul
     * abhängig ist.
     *
     * Beispiel:
     *
     * @var array
     */
    protected $require;
    
    /**
     * Die Kategorie in der sich das Modul befindet.
     *
     * @var string
     */
    protected $category;
    
    /**
     * HIER FEHLT EINE BESCHREIBUNG
     *
     * @var string
     */
    protected $type;
    
    /**
     * Mit welchen Version von Modified ist das Modul kompatible.
     *
     * Beispiel: [
     *  2.0.4.2,
     *  2.0.5.1
     * ]
     *
     * @var array
     */
    protected $modifiedCompatibility;
    
    /**
     * Installtionsanleitung in menschen lesbarer Form.
     *
     * @var string
     */
    protected $installation;

    /**
     * HIER FEHLT EINE BESCHREIBUNG
     *
     * @var string
     */
    protected $visibility;

    /**
     * HIER FEHLT EINE BESCHREIBUNG
     *
     * @var string
     */
    protected $price;

    /**
     * HIER FEHLT EINE BESCHREIBUNG
     *
     * @var array
     */
    protected $autoload;

    /**
     * Tags mit Komma getrennt.
     *
     * Beispiel: modul, modified, seo
     *
     * @var string
     */
    protected $tags;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $value): void
    {
        $this->name = $value;
    }

    public function getArchiveName(): string
    {
        return $this->archiveName;
    }

    public function setArchiveName(string $value): void
    {
        $this->archiveName = $value;
    }

    public function getSourceDir(): string
    {
        return $this->sourceDir;
    }

    public function setSourceDir(string $value): void
    {
        $this->sourceDir = $value;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $value): void
    {
        $this->version = $value;
    }

    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $value): void
    {
        $this->shortDescription = $value;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $value): void
    {
        $this->description = $value;
    }

    public function getDeveloper(): string
    {
        return $this->developer;
    }

    public function setDeveloper(string $value): void
    {
        $this->developer = $value;
    }

    public function getDeveloperWebsite(): string
    {
        return $this->developerWebsite;
    }

    public function setDeveloperWebsite(string $value): void
    {
        $this->developerWebsite = $value;
    }

    public function getWebsite(): string
    {
        return $this->website;
    }

    public function setWebsite(string $value): void
    {
        $this->website = $value;
    }

    public function getRequire(): array
    {
        return $this->require;
    }

    public function setRequire(array $value): void
    {
        $this->require = $value;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $value): void
    {
        $this->category = $value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $value): void
    {
        $this->type = $value;
    }

    public function getModifiedCompatibility(): array
    {
        return $this->modifiedCompatibility;
    }

    public function setModifiedCompatibility(array $value): void
    {
        $this->modifiedCompatibility = $value;
    }

    public function getInstallation(): string
    {
        return $this->installation;
    }

    public function setInstallation(string $value): void
    {
        $this->installation = $value;
    }

    public function getVisibility()
    {
        return $this->visibility;
    }

    public function setVisibility(string $value): void
    {
        $this->visibility = $value;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $value): void
    {
        $this->price = $value;
    }

    public function getAutoload(): array
    {
        return $this->autoload;
    }

    public function setAutoload(array $value): void
    {
        $this->autoload = $value;
    }

    public function getTags(): string
    {
        return $this->tags;
    }

    public function setTags(string $value): void
    {
        $this->tags = $value;
    }

    // public function loadFromJson($path)
    // {
    //     if (!file_exists($path)) {
    //         return false;
    //     }

    //     $json = file_get_contents($path);
    //     $array = json_decode($json, true);

    //     if (!$array) {
    //         return false;
    //     }

    //     return ModuleInfo::loadFromArray($array);
    // }

    // public function loadFromArray(array $array)
    // {
    //     $this->name = ArrayHelper::getIfSet($array, 'name');
    //     $this->archiveName = ArrayHelper::getIfSet($array, 'archiveName');
    //     $this->sourceDir = ArrayHelper::getIfSet($array, 'sourceDir', 'src');
    //     $this->version = ArrayHelper::getIfSet($array, 'version');
    //     $this->shortDescription = ArrayHelper::getIfSet($array, 'shortDescription');
    //     $this->description = ArrayHelper::getIfSet($array, 'description');
    //     $this->developer = ArrayHelper::getIfSet($array, 'developer');
    //     $this->developerWebsite = ArrayHelper::getIfSet($array, 'developerWebsite');
    //     $this->website = ArrayHelper::getIfSet($array, 'website');
    //     $this->require = ArrayHelper::getIfSet($array, 'require', []);
    //     $this->category = ArrayHelper::getIfSet($array, 'category');
    //     $this->type = ArrayHelper::getIfSet($array, 'type');
    //     $this->modifiedCompatibility = ArrayHelper::getIfSet($array, 'modifiedCompatibility', []);
    //     $this->installation = ArrayHelper::getIfSet($array, 'installation');
    //     $this->visibility = ArrayHelper::getIfSet($array, 'visibility');
    //     $this->price = ArrayHelper::getIfSet($array, 'price');
    //     $this->autoload = ArrayHelper::getIfSet($array, 'autoload');
    //     $this->tags = ArrayHelper::getIfSet($array, 'tags');

    //     return true;
    // }

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
            'autoload' => $this->autoload,
            'tags' => $this->tags
        ];
    }
}
