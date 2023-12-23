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

use RobinTheHood\ModifiedModuleLoaderClient\Api\V1\ApiRequest;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Filter;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\ParseErrorException;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;

class MmlcVersionInfoLoader
{
    /** @var ApiRequest */
    private $apiRequest;

    /** @var Parser */
    private $parser;

    /** @var Filter */
    private $filter;

    public static function createLoader(): MmlcVersionInfoLoader
    {
        $mmlcVersionInfoLoader = new MmlcVersionInfoLoader(
            new ApiRequest(),
            Parser::create(),
            Filter::create(Comparator::CARET_MODE_STRICT)
        );

        return $mmlcVersionInfoLoader;
    }

    public function __construct(ApiRequest $apiRequest, Parser $parser, Filter $filter)
    {
        $this->apiRequest = $apiRequest;
        $this->parser = $parser;
        $this->filter = $filter;
    }

    /**
     * Fetch all MmlcVersionInfo via api from MMLS
     *
     * @return MmlcVersionInfo[] all available MmlcVersionInfo
     */
    public function getAll(): array
    {
        $result = $this->apiRequest->getAllVersions();

        $content = $result['content'] ?? [];
        if (!$content) {
            return [];
        }

        $mmlcVersionInfos = [];
        foreach ($content as $mmlcVersionInfoAsArray) {
            if (!array_key_exists('version', $mmlcVersionInfoAsArray)) {
                continue;
            }

            if (!array_key_exists('fileName', $mmlcVersionInfoAsArray)) {
                continue;
            }

            try {
                $this->parser->parse($mmlcVersionInfoAsArray['version']);
            } catch (ParseErrorException $e) {
                continue;
            }

            $mmlcVersionInfo = new MmlcVersionInfo();
            $mmlcVersionInfo->version = $mmlcVersionInfoAsArray['version'];
            $mmlcVersionInfo->fileName = $mmlcVersionInfoAsArray['fileName'];
            $mmlcVersionInfos[] = $mmlcVersionInfo;
        }
        return $mmlcVersionInfos;
    }

    /**
     * Returns the latest MmlcVersionInfo
     *
     * @param bool $latest
     *
     * @return ?MmlcVersionInfo Returns the latest MmlcVersionInfo
     */
    public function getNewest($latest = false): ?MmlcVersionInfo
    {
        $mmlcVersionInfos = $this->getAll();
        $versionStrings = $this->getVersionStringsFromMmlcVersionInfos($mmlcVersionInfos);

        if (!$latest) {
            $versionStrings = $this->filter->stable($versionStrings);
        }

        $versionString = $this->filter->latest($versionStrings);
        $mmlcVersionInfo = $this->getMmlcVersionInfoByVersionString($versionString, $mmlcVersionInfos);

        return $mmlcVersionInfo;
    }

    /**
     * @param string $installtedVersionString
     * @param bool $$latest
     *
     * @return ?MmlcVersionInfo
     */
    public function getNextNewest(string $installtedVersionString, bool $latest = false): ?MmlcVersionInfo
    {
        $versionInfos = $this->getAll();
        $versionStrings = $this->getVersionStringsFromMmlcVersionInfos($versionInfos);

        if (!$latest) {
            $versionStrings = $this->filter->stable($versionStrings);
        }

        $version = $this->parser->parse($installtedVersionString);
        $constrain = '<=' . $version->nextMinor();

        $versionString = $this->filter->latestByConstraint($constrain, $versionStrings);
        $versionInfo = $this->getMmlcVersionInfoByVersionString($versionString, $versionInfos);

        return $versionInfo;
    }

    /**
     * @param string $versionString
     * @param MmlcVersionInfo[] $mmlcVersionInfos
     *
     * @return ?MmlcVersionInfo
     */
    private function getMmlcVersionInfoByVersionString(string $versionString, array $mmlcVersionInfos): ?MmlcVersionInfo
    {
        foreach ($mmlcVersionInfos as $mmlcVersionInfo) {
            if ($mmlcVersionInfo->version === $versionString) {
                return $mmlcVersionInfo;
            }
        }
        return null;
    }

    /**
     * @param MmlcVersionInfo[] $mmlcVersionInfos
     *
     * @return string[]
     */
    private function getVersionStringsFromMmlcVersionInfos(array $mmlcVersionInfos): array
    {
        $versionStrings = [];
        foreach ($mmlcVersionInfos as $mmlcVersionInfo) {
            $versionStrings[] = $mmlcVersionInfo->version;
        }
        return $versionStrings;
    }
}
