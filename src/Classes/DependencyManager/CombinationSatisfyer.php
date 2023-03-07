<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace RobinTheHood\ModifiedModuleLoaderClient\DependencyManager;

class CombinationSatisfyer
{
    public function satisfiesCominationsFromModuleTreeNodes(array $moduleTreeNodes, array $combinations): array
    {
        foreach ($combinations as $combination) {
            $result = $this->satisfiesCominationFromModuleTreeNodes($moduleTreeNodes, $combination);
            if ($result) {
                return $combination;
            }
        }
        return [];
    }

    public function satisfiesCominationsFromModuleTreeNode(ModuleTreeNode $moduleTreeNode, array $combinations): array
    {
        foreach ($combinations as $combination) {
            $result = $this->satisfiesCominationFromModuleTreeNode($moduleTreeNode, $combination);
            if ($result) {
                return $combination;
            }
        }
        return [];
    }

    public function satisfiesCominationsFromModuleWithIterator(
        ModuleTreeNode $moduleTreeNode,
        CombinationIterator $combinationIterator
    ): array {
        while (true) {
            $combination = $combinationIterator->current();
            $result = $this->satisfiesCominationFromModuleTreeNode($moduleTreeNode, $combination);
            if ($result) {
                return $combination;
            }

            $combinationIterator->next();
            if ($combinationIterator->isStart()) {
                return [];
            }
        }
    }

    public function satisfiesCominationFromModuleTreeNode(ModuleTreeNode $moduleTreeNode, array $combination): bool
    {
        // Context: Module
        $archiveName = $moduleTreeNode->archiveName;
        $selectedVersion = $combination[$archiveName];
        foreach ($moduleTreeNode->moduleVersions as $moduleVersion) {
            // Context: Version
            if ($moduleVersion->version === $selectedVersion) {
                return $this->satisfiesCominationFromModuleTreeNodes($moduleVersion->require, $combination);
            }
        }
        return false;
    }

    public function satisfiesCominationFromModuleTreeNodes(array $moduleTreeNodes, array $combination): bool
    {
        // Context: Expanded
        $moduleResult = true;
        foreach ($moduleTreeNodes as $moduleTreeNode) {
            $moduleResult =
                $moduleResult && $this->satisfiesCominationFromModuleTreeNode($moduleTreeNode, $combination);
        }
        return $moduleResult;
    }

    // public function satisfiesCominationFromModuleTreeNodes(array $moduleTreeNodes, array $combination): bool
    // {
    //     // Context: Expanded
    //     $moduleResult = true;
    //     foreach ($moduleTreeNodes as $moduleTreeNode) {
    //         // Context: Module
    //         $archiveName = $moduleTreeNode->archiveName;
    //         $selectedVersion = $combination[$archiveName];
    //         $versionResult = false;
    //         foreach ($moduleTreeNode->moduleVersions as $moduleVersion) {
    //             // Context: Version
    //             if ($moduleVersion->version === $selectedVersion) {
    //                 $versionResult = $this->satisfiesCominationFromModuleTreeNodes($moduleVersion->require, $combination);
    //                 break;
    //             }
    //         }
    //         $moduleResult = $moduleResult && $versionResult;
    //     }
    //     return $moduleResult;
    // }
}
