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

class Semver
{
    public static function parse($string)
    {
        $version = [
            'major' => 0,
            'minor' => 0,
            'path' => 0
        ];

        $parts = explode('.', $string);
        if (count($parts) == 3) {
            $version['major'] = (int) $parts[0];
            $version['minor'] = (int) $parts[1];
            $version['path'] = (int) $parts[2];
        }

        return $version;
    }

    public static function greaterThan($versionString1, $versionString2)
    {
        if ($versionString1 == 'auto' && $versionString2 != 'auto') {
            return true;
        }

        if ($versionString2 == 'auto' && $versionString1 != 'auto') {
            return false;
        }

        $version1 = self::parse($versionString1);
        $version2 = self::parse($versionString2);

        if ($version1['major'] > $version2['major']) {
            return true;
        }

        if ($version1['major'] == $version2['major'] &&
            $version1['minor'] > $version2['minor'] ) {
            return true;
        }

        if ($version1['major'] == $version2['major'] &&
            $version1['minor'] == $version2['minor'] &&
            $version1['path'] > $version2['path'] ) {
            return true;
        }

        return false;
    }

    public static function equalTo($versionString1, $versionString2)
    {
        if ($versionString1 == 'auto' && $versionString2 == 'auto') {
            return true;
        }

        $version1 = self::parse($versionString1);
        $version2 = self::parse($versionString2);

        if ($version1['major'] != $version2['major']) {
            return false;
        }

        if ($version1['minor'] != $version2['minor'] ) {
            return false;
        }

        if ($version1['path'] != $version2['path'] ) {
            return false;
        }

        return true;
    }

    public static function greaterThanOrEqualTo($versionString1, $versionString2)
    {
        if (self::greaterThan($versionString1, $versionString2)) {
            return true;
        }

        if (self::equalTo($versionString1, $versionString2)) {
            return true;
        }

        return false;
    }

    public static function lessThan($versionString1, $versionString2)
    {
        if (!self::greaterThanOrEqualTo($versionString1, $versionString2)) {
            return true;
        }

        return false;
    }

    public static function lessThanOrEqualTo($versionString1, $versionString2)
    {
        if (!self::greaterThan($versionString1, $versionString2)) {
            return true;
        }

        return false;
    }

    public static function notEqualTo($versionString1, $versionString2)
    {
        if (!self::equalTo($versionString1, $versionString2)) {
            return true;
        }

        return false;
    }

    public static function highest($versionStrings)
    {
        $versionStrings = self::rsort($versionStrings);
        return $versionStrings[0];
    }

    public static function lowest($versionStrings)
    {
        $versionStrings = self::sort($versionStrings);
        return $versionStrings[0];
    }

    // Testet ob Version1 mindestens das kann, was auch Version2 kann.
    // Version1 darf auch mehr können als das was Version2 kann,
    // aber nicht weniger.
    public static function isCompatible($versionString1, $versionString2)
    {
        if ($versionString1 == 'auto') {
            return true;
        }

        $version1 = self::parse($versionString1);
        $version2 = self::parse($versionString2);

        if ($version1['major'] != $version2['major']) {
            return false;
        }

        return self::greaterThanOrEqualTo($versionString1, $versionString2);
    }

    public static function satisfies($versionString1, $constrain)
    {
        if ($constrain[0] == '^') { // Ist Buchstabe an Index 0 = ^
            $versionString2 = str_replace('^', '', $constrain);
            return self::isCompatible($versionString1, $versionString2);
        } else {
            $versionString2 = $constrain;
            return self::equalTo($versionString1, $versionString2);
        }
    }

    public static function sort($versionStrings)
    {
        usort($versionStrings, [self, 'compareAsc']);
        return $versionStrings;
    }

    public static function rsort($versionStrings)
    {
        usort($versionStrings, [self, 'compareDes']);
        return $versionStrings;
    }

    private static function compareAsc($versionString1, $versionString2)
    {
        if (self::greaterThan($versionString1, $versionString2)) {
            return 1;
        }

        return -1;
    }

    private static function compareDes($versionString1, $versionString2)
    {
        if (self::greaterThan($versionString1, $versionString2)) {
            return -1;
        }

        return 1;
    }
}

// var_dump(Semver::greaterThan('1.2.3', '1.2.3')); var_dump('expect: false');
// var_dump(Semver::greaterThan('1.2.10', '1.2.9')); var_dump('expect: true');
// var_dump(Semver::greaterThan('1.3.3', '1.2.3')); var_dump('expect: true');
// var_dump(Semver::greaterThan('2.2.3', '1.2.3')); var_dump('expect: true');
// var_dump(Semver::greaterThan('2.2.9', '2.2.10')); var_dump('expect: false');
//
// var_dump(Semver::equalTo('1.2.3', '1.2.4')); var_dump('expect: false');
// var_dump(Semver::equalTo('1.2.4', '1.2.3')); var_dump('expect: false');
// var_dump(Semver::equalTo('1.2.3', '1.2.3')); var_dump('expect: true');
//
// var_dump(Semver::greaterThanOrEqualTo('1.2.3', '1.2.3')); var_dump('expect: true');
// var_dump(Semver::greaterThanOrEqualTo('1.2.4', '1.2.3')); var_dump('expect: true');
// var_dump(Semver::greaterThanOrEqualTo('1.3.3', '1.2.3')); var_dump('expect: true');
// var_dump(Semver::greaterThanOrEqualTo('2.2.3', '1.2.3')); var_dump('expect: true');
// var_dump(Semver::greaterThanOrEqualTo('2.2.3', '2.2.4')); var_dump('expect: false');
//
// var_dump(Semver::lessThan('1.2.3', '1.2.3')); var_dump('expect: false');
// var_dump(Semver::lessThan('1.2.10', '1.2.9')); var_dump('expect: false');
// var_dump(Semver::lessThan('1.10.3', '1.9.3')); var_dump('expect: false');
// var_dump(Semver::lessThan('10.2.3', '9.2.3')); var_dump('expect: false');
// var_dump(Semver::lessThan('2.2.9', '2.2.10')); var_dump('expect: true');
//
// var_dump(Semver::lessThanOrEqualTo('1.2.3', '1.2.3')); var_dump('expect: true');
// var_dump(Semver::lessThanOrEqualTo('1.2.10', '1.2.9')); var_dump('expect: false');
// var_dump(Semver::lessThanOrEqualTo('1.10.3', '1.9.3')); var_dump('expect: false');
// var_dump(Semver::lessThanOrEqualTo('10.2.3', '9.2.3')); var_dump('expect: false');
// var_dump(Semver::lessThanOrEqualTo('2.2.9', '2.2.10')); var_dump('expect: true');
//
// var_dump(Semver::notEqualTo('1.2.3', '1.2.4')); var_dump('expect: true');
// var_dump(Semver::notEqualTo('1.2.4', '1.2.3')); var_dump('expect: true');
// var_dump(Semver::notEqualTo('1.2.3', '1.2.3')); var_dump('expect: false');
//
// var_dump(Semver::sort(['17.111.9', '1.2.3', '18.22.10', '18.33.10', '18.22.9']));
// var_dump(Semver::rsort(['17.111.9', '1.2.3', '18.22.9', '18.33.10', '18.22.10']));
