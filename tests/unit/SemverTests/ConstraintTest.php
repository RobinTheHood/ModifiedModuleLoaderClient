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

namespace RobinTheHood\ModifiedModuleLoaderClient\Tests\Unit\SemverTests;

use PHPUnit\Framework\TestCase;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Constraint;

class ConstraintTest extends TestCase
{
    public function testResolveCaretRangeWithMajor()
    {
        $range = '^1.0.0';
        $expected = '>=1.0.0,<2.0.0';
        $this->assertEquals($expected, Constraint::resolveCaretRange($range));
    }

    public function testResolveCaretRangeWithMajorMinor()
    {
        $range = '^1.2.0';
        $expected = '>=1.2.0,<2.0.0';
        $this->assertEquals($expected, Constraint::resolveCaretRange($range));
    }

    public function testResolveCaretRangeWithMajorMinorPatch()
    {
        $range = '^1.2.3';
        $expected = '>=1.2.3,<2.0.0';
        $this->assertEquals($expected, Constraint::resolveCaretRange($range));
    }

    public function testResolveCaretRangeWithSuffix()
    {
        $range = '^1.2.3-alpha';
        $expected = '>=1.2.3-alpha,<2.0.0';
        $this->assertEquals($expected, Constraint::resolveCaretRange($range));
    }

    public function testResolveCaretRangeWithMajorAndTag()
    {
        $range = '^1.0.0-beta';
        $expected = '>=1.0.0-beta,<2.0.0';
        $this->assertEquals($expected, Constraint::resolveCaretRange($range));
    }

    public function testResolveCaretRangeWithMajorMinorAndTag()
    {
        $range = '^1.2.0-rc';
        $expected = '>=1.2.0-rc,<2.0.0';
        $this->assertEquals($expected, Constraint::resolveCaretRange($range));
    }

    public function testResolveCaretRangeWithMajorMinorPatchAndTag()
    {
        $range = '^1.2.3-dev';
        $expected = '>=1.2.3-dev,<2.0.0';
        $this->assertEquals($expected, Constraint::resolveCaretRange($range));
    }

    public function testResolveCaretPreRelease()
    {
        // Test resolving a range with a caret and a major version of 0
        $this->assertEquals(">=0.2.0,<0.3.0", Constraint::resolveCaretRange("^0.2.0"));

        // Test resolving a range with a caret and a minor version of 0 and a patch version of 1
        $this->assertEquals(">=0.0.1,<0.1.0", Constraint::resolveCaretRange("^0.0.1"));
    }

    public function testCreateConstraintFromConstraints()
    {
        $constraints = [
            '>1.0', '>=2.3,<4.0',
        ];
        $expected = '>1.0, >=2.3,<4.0';
        $this->assertEquals($expected, Constraint::createConstraintFromConstraints($constraints));
    }

    public function testCreateConstraintFromConstraintsWithDuplicateVersions()
    {
        $constraints = [
            '>=2.3,<4.0', '>=2.3,<=3.0',
        ];
        $expected = '>=2.3,<4.0, >=2.3,<=3.0';
        $this->assertEquals($expected, Constraint::createConstraintFromConstraints($constraints));
    }

    public function testCreateConstraintFromConstraintsWithOneConstraint()
    {
        $constraints = [
            '>=1.0',
        ];
        $expected = '>=1.0';
        $this->assertEquals($expected, Constraint::createConstraintFromConstraints($constraints));
    }

    public function testCreateConstraintFromConstraintsWithEmptyArray()
    {
        $constraints = [];
        $expected = '';
        $this->assertEquals($expected, Constraint::createConstraintFromConstraints($constraints));
    }
}
