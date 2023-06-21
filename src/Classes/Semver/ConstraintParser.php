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

use RobinTheHood\ModifiedModuleLoaderClient\Semver\ParseErrorException;

class ConstraintParser
{
    /** @var Parser $parser */
    private $parser;

    public static function create(int $mode): ConstraintParser
    {
        $parser = Parser::create();
        return new ConstraintParser($parser);
    }

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parse(string $constraintString): Constraint
    {
        $constraint = new Constraint();
        $constraint->constraintString = $constraintString;

        if (strpos($constraintString, '||')) {
            $constraint->type = Constraint::TYPE_OR;
            $subConstraintStrings = explode('||', $constraintString);
            foreach ($subConstraintStrings as $subConstraintString) {
                $subConstraintString = trim($subConstraintString);
                $subConstraint = $this->parse($subConstraintString);
                $constraint->constraints[] = $subConstraint;
            }
            return $constraint;
        } elseif (strpos($constraintString, ',')) {
            $constraint->type = Constraint::TYPE_AND;
            $subConstraintStrings = explode(',', $constraintString);
            foreach ($subConstraintStrings as $subConstraintString) {
                $subConstraintString = trim($subConstraintString);
                $subConstraint = $this->parse($subConstraintString);
                $constraint->constraints[] = $subConstraint;
            }
            return $constraint;
        } elseif (strpos($constraintString, '^') === 0) {
            $constraint->type = Constraint::TYPE_CARET;
            $versionString = substr($constraintString, 1);
            $version = $this->parser->parse($versionString);
            $constraint->version = $version;
            $constraint->versionString = $versionString;
            return $constraint;
        } elseif (strpos($constraintString, '<=') === 0) {
            $constraint->type = Constraint::TYPE_LESS_OR_EQUAL;
            $versionString = substr($constraintString, 2);
            $version = $this->parser->parse($versionString);
            $constraint->version = $version;
            $constraint->versionString = $versionString;
            return $constraint;
        } elseif (strpos($constraintString, '<') === 0) {
            $constraint->type = Constraint::TYPE_LESS;
            $versionString = substr($constraintString, 1);
            $version = $this->parser->parse($versionString);
            $constraint->version = $version;
            $constraint->versionString = $versionString;
            return $constraint;
        } elseif (strpos($constraintString, '>=') === 0) {
            $constraint->type = Constraint::TYPE_GREATER_OR_EQUAL;
            $versionString = substr($constraintString, 2);
            $version = $this->parser->parse($versionString);
            $constraint->version = $version;
            $constraint->versionString = $versionString;
            return $constraint;
        } elseif (strpos($constraintString, '>') === 0) {
            $constraint->type = Constraint::TYPE_GREATER;
            $versionString = substr($constraintString, 1);
            $version = $this->parser->parse($versionString);
            $constraint->version = $version;
            $constraint->versionString = $versionString;
            return $constraint;
        } elseif (strpos($constraintString, 'auto') === 0) {
            $constraint->type = Constraint::TYPE_EQUAL;
            $versionString = substr($constraintString, 1);
            //$version = $this->parser->parse($versionString);
            //$constraint->version = null; //$version;
            $constraint->versionString = 'auto';
            return $constraint;
        }

        try {
            $constraint->type = Constraint::TYPE_EQUAL;
            $versionString = $constraintString;
            $version = $this->parser->parse($versionString);
            $constraint->version = $version;
            $constraint->versionString = $versionString;
            return $constraint;
        } catch (ParseErrorException $e) {
            throw new ParseErrorException('Unsupported version constraint ' . $constraintString);
        }
    }
}
