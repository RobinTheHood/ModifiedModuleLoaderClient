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

namespace RobinTheHood\ModifiedModuleLoaderClient\Semver;

use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;

class Comparator
{
    public const CARET_MODE_LAX = 0;
    public const CARET_MODE_STRICT = 1;

    /** @var Parser */
    protected $parser;

    /** @var ConstraintParser */
    protected $constraintParser;

    /** @var TagComparator */
    protected $tagComparator;

    /** @var int */
    private $mode = self::CARET_MODE_STRICT;

    public static function create(int $mode): Comparator
    {
        $parser = Parser::create();
        $constraintParser = ConstraintParser::create($mode);
        $tagComparator = TagComparator::create();
        return new Comparator($parser, $constraintParser, $tagComparator, $mode);
    }

    public function __construct(
        Parser $parser,
        ConstraintParser $constraintParser,
        TagComparator $tagComparator,
        int $mode
    ) {
        $this->parser = $parser;
        $this->constraintParser = $constraintParser;
        $this->tagComparator = $tagComparator;
        $this->mode = $mode;
    }

    public function greaterThan(string $versionString1, string $versionString2): bool
    {
        if ($versionString1 == 'auto' && $versionString2 != 'auto') {
            return true;
        }

        if ($versionString2 == 'auto' && $versionString1 != 'auto') {
            return false;
        }

        if ($versionString1 == 'auto' && $versionString2 == 'auto') {
            return true;
        }

        $version1 = $this->parser->parse($versionString1);
        $version2 = $this->parser->parse($versionString2);

        if ($version1->getMajor() > $version2->getMajor()) {
            return true;
        }

        if (
            $version1->getMajor() == $version2->getMajor() &&
            $version1->getMinor() > $version2->getMinor()
        ) {
            return true;
        }

        if (
            $version1->getMajor() == $version2->getMajor() &&
            $version1->getMinor() == $version2->getMinor() &&
            $version1->getPatch() > $version2->getPatch()
        ) {
            return true;
        }

        if (
            $version1->getMajor() == $version2->getMajor() &&
            $version1->getMinor() == $version2->getMinor() &&
            $version1->getPatch() == $version2->getPatch() &&
            $this->tagComparator->greaterThan($version1->getTag(), $version2->getTag())
        ) {
            return true;
        }

        return false;
    }

    public function equalTo(string $versionString1, string $versionString2): bool
    {
        if ($versionString1 == 'auto' && $versionString2 == 'auto') {
            return true;
        } elseif ($versionString1 == 'auto' && $versionString2 != 'auto') {
            return false;
        } elseif ($versionString1 != 'auto' && $versionString2 == 'auto') {
            return false;
        }

        $version1 = $this->parser->parse($versionString1);
        $version2 = $this->parser->parse($versionString2);

        if ($version1->getMajor() !== $version2->getMajor()) {
            return false;
        }

        if ($version1->getMinor() !== $version2->getMinor()) {
            return false;
        }

        if ($version1->getPatch() !== $version2->getPatch()) {
            return false;
        }

        if ($version1->getTag() !== $version2->getTag()) {
            return false;
        }

        return true;
    }

    public function greaterThanOrEqualTo(string $versionString1, string $versionString2): bool
    {
        if ($this->greaterThan($versionString1, $versionString2)) {
            return true;
        }

        if ($this->equalTo($versionString1, $versionString2)) {
            return true;
        }

        return false;
    }

    public function lessThan(string $versionString1, string $versionString2): bool
    {
        if (!$this->greaterThanOrEqualTo($versionString1, $versionString2)) {
            return true;
        }

        return false;
    }

    public function lessThanOrEqualTo(string $versionString1, string $versionString2): bool
    {
        if (!$this->greaterThan($versionString1, $versionString2)) {
            return true;
        }

        return false;
    }

    public function notEqualTo(string $versionString1, string $versionString2): bool
    {
        if (!$this->equalTo($versionString1, $versionString2)) {
            return true;
        }

        return false;
    }

    // Testet ob Version1 mindestens das kann, was auch Version2 kann.
    // Version1 darf auch mehr können als das was Version2 kann,
    // aber nicht weniger.
    public function isCompatible(string $versionString1, string $versionString2): bool
    {
        if ($versionString1 == 'auto') {
            return true;
        }

        $version1 = $this->parser->parse($versionString1);
        $version2 = $this->parser->parse($versionString2);

        $majorCheck = $version1->getMajor() == $version2->getMajor();
        $minorCheck = $version1->getMinor() == $version2->getMinor();
        $patchCheck = $version1->getPatch() == $version2->getPatch();

        $strict = $this->mode === self::CARET_MODE_STRICT;

        if ($version1->getMajor() >= 1) { // ^1.0.0
            if (!$majorCheck) {
                return false;
            }
        } elseif ($strict && $version1->getMajor() == 0 && $version1->getMinor() >= 1) { // ^0.1.0
            if (!$majorCheck || !$minorCheck) {
                return false;
            }
        } elseif ($strict && $version1->getMajor() == 0 && $version1->getMinor() == 0) { // ^0.0.0
            if (!$majorCheck || !$minorCheck || !$patchCheck) {
                return false;
            }
        }

        return $this->greaterThanOrEqualTo($versionString1, $versionString2);
    }

    public function satisfies(string $versionString1, string $constrainString): bool
    {
        try {
            $constraint = $this->constraintParser->parse($constrainString);
        } catch (ParseErrorException $e) {
            return false;
        }

        if ($constraint->type === Constraint::TYPE_OR) {
            return $this->satisfiesOr($versionString1, $constraint);
        }

        if ($constraint->type === Constraint::TYPE_AND) {
            return $this->satisfiesAnd($versionString1, $constraint);
        }

        if ($constraint->type === Constraint::TYPE_LESS_OR_EQUAL) {
            return $this->lessThanOrEqualTo($versionString1, $constraint->versionString);
        } elseif ($constraint->type === Constraint::TYPE_LESS) {
            return $this->lessThan($versionString1, $constraint->versionString);
        } elseif ($constraint->type === Constraint::TYPE_GREATER_OR_EQUAL) {
            return $this->greaterThanOrEqualTo($versionString1, $constraint->versionString);
        } elseif ($constraint->type === Constraint::TYPE_GREATER) {
            return $this->greaterThan($versionString1, $constraint->versionString);
        } elseif ($constraint->type === Constraint::TYPE_CARET) {
            return $this->isCompatible($versionString1, $constraint->versionString);
        } elseif ($constraint->type === Constraint::TYPE_EQUAL) {
            return $this->equalTo($versionString1, $constraint->versionString);
        }

        return false;
    }

    /**
     * Can satisfy multiple constraints with OR (||)
     *
     * Example: ^7.4 || ^8.0
     */
    public function satisfiesOr(string $versionString1, Constraint $constraint): bool
    {
        foreach ($constraint->constraints as $constraint) {
            if ($this->satisfies($versionString1, $constraint->constraintString)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Can satisfy multiple constraints with AND (,)
     *
     * Example: ^7.4, ^8.0
     */
    public function satisfiesAnd(string $versionString1, Constraint $constraint): bool
    {
        foreach ($constraint->constraints as $constraint) {
            if (!$this->satisfies($versionString1, $constraint->constraintString)) {
                return false;
            }
        }
        return true;
    }
}
