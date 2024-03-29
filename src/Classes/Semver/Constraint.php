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

class Constraint
{
    public const TYPE_EQUAL = '=';
    public const TYPE_OR = '||';
    public const TYPE_AND = ',';
    public const TYPE_CARET = '^';
    public const TYPE_LESS_OR_EQUAL = '<=';
    public const TYPE_LESS = '<';
    public const TYPE_GREATER_OR_EQUAL = '>=';
    public const TYPE_GREATER = '>';

    /** @var string $type */
    public $type;

    /** @var string $constraintString */
    public $constraintString;

    /** @var Version $version */
    public $version;

    /** @var string $versionString */
    public $versionString;

    /** @var Constraint[] $constraints */
    public $constraints = [];

    public static function createConstraintFromConstraints(array $constraints): string
    {
        $constraint = '';
        foreach ($constraints as $version) {
            if ($constraint === '') {
                $constraint = $version;
            } else {
                $constraint .= ', ' . $version;
            }
        }
        return $constraint;
    }

    public static function resolveCaretRange(string $range): string
    {
        if (preg_match('/^\^(?<major>\d+)(\.(?<minor>\d+))?(\.(?<patch>\d+))?(?<suffix>.*)$/', $range, $matches)) {
            $major = intval($matches['major']);
            $minor = isset($matches['minor']) ? intval($matches['minor']) : 0;
            $patch = isset($matches['patch']) ? intval($matches['patch']) : 0;
            $suffix = $matches['suffix'];

            $lower = sprintf("%d.%d.%d%s", $major, $minor, $patch, $suffix);

            if ($major == 0) {
                $upper = sprintf("%d.%d.%d", $major, $minor + 1, 0);
            } else {
                $upper = sprintf("%d.%d.%s", $major + 1, 0, '0');
            }

            return ">=$lower,<$upper";
        }

        return $range;
    }

    public static function resolve(string $constaint): string
    {
        return self::resolveCaretRange($constaint);
    }
}
