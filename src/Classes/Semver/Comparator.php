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
    protected $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
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
            (new TagComparator())->greaterThan($version1->getTag(), $version2->getTag())
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

        if ($version1->getMajor() != $version2->getMajor()) {
            return false;
        }

        return $this->greaterThanOrEqualTo($versionString1, $versionString2);
    }

    public function satisfies(string $versionString1, string $constrainString): bool
    {
        $constraintParser = new ConstraintParser($this->parser);
        try {
            $constraint = $constraintParser->parse($constrainString);
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
