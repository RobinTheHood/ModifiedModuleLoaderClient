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

/**
 * Das CombinationSatisfyerResult Objekt liefert Information zum Ergebnis des CombinationStisfyer.
 */
class CombinationSatisfyerResult
{
    public const RESULT_COMBINATION_NOT_FOUND = 0;
    public const RESULT_COMBINATION_FOUND = 1;

    /** @var int */
    public $result = -1;

    /**
     * @var ?Combination $testCombination Beinhaltet eine Kombination an allen Modulen und PHP, MMLC und modified
     *      Versionen die durch ein SystemSet als Auswahl standen. Welche Kombination verwendet wird hängt vom
     *      CombinationStisfyer ab. Oft ist es die letzte Kombination die probiert wurde.
     */
    public $testCombination = null;

    /**
     * @var ?Combination $foundCombination Enthält nur die Elemente (Module, PHP, MMLC und modified Version) aus
     *      $testCombination die nötig sind, um die Voraussetung für ein Modul zu erfüllen. Ist das Ergbnis
     *      RESULT_COMBINATION_NOT_FOUND, fehlen in $foundCombination Elemente.
     */
    public $foundCombination = null;

    /** @var ?FailLog */
    public $failLog = null;

    public function __construct(
        int $result,
        ?Combination $testCombination,
        ?Combination $foundCombination,
        ?FailLog $failLog
    ) {
        $this->result = $result;
        $this->testCombination = $testCombination;
        $this->foundCombination = $foundCombination;
        $this->failLog = $failLog;
    }
}
