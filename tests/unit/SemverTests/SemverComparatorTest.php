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
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Comparator;
use RobinTheHood\ModifiedModuleLoaderClient\Semver\Parser;

class SemverComparatorTest extends TestCase
{
    public $comparator;

    protected function setUp(): void
    {
        $this->comparator = new Comparator(new Parser());
    }

    public function testSemverCanHandleGreaterThan()
    {
        $this->assertTrue($this->comparator->greaterThan('auto', '1.2.3'));
        $this->assertFalse($this->comparator->greaterThan('1.2.3', 'auto'));

        $this->assertFalse($this->comparator->greaterThan('1.2.3', '1.2.3'));
        $this->assertFalse($this->comparator->greaterThan('2.2.9', '2.2.10'));
        $this->assertTrue($this->comparator->greaterThan('1.2.10', '1.2.9'));
        $this->assertTrue($this->comparator->greaterThan('1.3.3', '1.2.3'));
        $this->assertTrue($this->comparator->greaterThan('2.2.3', '1.2.3'));

        $this->assertTrue($this->comparator->greaterThan('v1.2.10', 'v1.2.9'));
        $this->assertTrue($this->comparator->greaterThan('v1.3.3', '1.2.3'));
        $this->assertTrue($this->comparator->greaterThan('2.2.3', 'v1.2.3'));

        $this->assertTrue($this->comparator->greaterThan('1.2.3', '1.2.3-beta'));
        $this->assertTrue($this->comparator->greaterThan('1.2.3-beta', 'v1.2.3-alpha'));
        $this->assertTrue($this->comparator->greaterThan('1.2.3-rc.1', 'v1.2.3-beta'));

        $this->assertFalse($this->comparator->greaterThan('1.2.3-beta', 'v1.2.3'));
    }

    public function testSemverCanHandleEqualTo()
    {
        $this->assertTrue($this->comparator->equalTo('auto', 'auto'));
        $this->assertFalse($this->comparator->equalTo('auto', '1.2.3'));
        $this->assertFalse($this->comparator->equalTo('1.2.3', 'auto'));

        $this->assertFalse($this->comparator->equalTo('1.2.3', '1.2.4'));
        $this->assertFalse($this->comparator->equalTo('1.2.4', '1.2.3'));
        $this->assertTrue($this->comparator->equalTo('1.2.3', '1.2.3'));

        $this->assertTrue($this->comparator->equalTo('v1.2.3', 'v1.2.3'));
        $this->assertTrue($this->comparator->equalTo('1.2.3', 'v1.2.3'));
        $this->assertTrue($this->comparator->equalTo('v1.2.3', '1.2.3'));

        $this->assertTrue($this->comparator->equalTo('v1.2.3-alpha', '1.2.3-alpha'));
        $this->assertTrue($this->comparator->equalTo('1.2.3-beta', '1.2.3-beta'));
        $this->assertTrue($this->comparator->equalTo('1.2.3-rc.1', 'v1.2.3-rc.1'));
    }


    public function testSemverCanHandleGreaterThanOrEqualTo()
    {
        $this->assertTrue($this->comparator->greaterThanOrEqualTo('1.2.3', '1.2.3'));
        $this->assertTrue($this->comparator->greaterThanOrEqualTo('1.2.4', '1.2.3'));
        $this->assertTrue($this->comparator->greaterThanOrEqualTo('1.3.3', '1.2.3'));
        $this->assertTrue($this->comparator->greaterThanOrEqualTo('2.2.3', '1.2.3'));
        $this->assertFalse($this->comparator->greaterThanOrEqualTo('2.2.3', '2.2.4'));
    }

    public function testSemverCanHandleLessThan()
    {
        $this->assertFalse($this->comparator->lessThan('auto', '1.2.3'));
        $this->assertTrue($this->comparator->lessThan('1.2.3', 'auto'));

        $this->assertFalse($this->comparator->lessThan('1.2.3', '1.2.3'));
        $this->assertFalse($this->comparator->lessThan('1.2.10', '1.2.9'));
        $this->assertFalse($this->comparator->lessThan('1.10.3', '1.9.3'));
        $this->assertFalse($this->comparator->lessThan('10.2.3', '9.2.3'));
        $this->assertTrue($this->comparator->lessThan('2.2.9', '2.2.10'));

        $this->assertTrue($this->comparator->lessThan('2.2.9-beta', '2.2.9'));
    }

    public function testSemverCanHandleLessThanOrEqualTo()
    {
        $this->assertTrue($this->comparator->lessThanOrEqualTo('1.2.3', '1.2.3'));
        $this->assertTrue($this->comparator->lessThanOrEqualTo('2.2.9', '2.2.10'));
        $this->assertFalse($this->comparator->lessThanOrEqualTo('1.2.10', '1.2.9'));
        $this->assertFalse($this->comparator->lessThanOrEqualTo('1.10.3', '1.9.3'));
        $this->assertFalse($this->comparator->lessThanOrEqualTo('10.2.3', '9.2.3'));
    }

    public function testSemverCanHandleNotEqualTo()
    {
        $this->assertTrue($this->comparator->notEqualTo('1.2.3', '1.2.4'));
        $this->assertTrue($this->comparator->notEqualTo('1.2.4', '1.2.3'));
        $this->assertFalse($this->comparator->notEqualTo('1.2.3', '1.2.3'));
    }

    public function testThatVersionAIsCompatibleWithVersionB()
    {
        $this->assertTrue($this->comparator->isCompatible('auto', '3.3.3'));

        $this->assertTrue($this->comparator->isCompatible('3.3.3', '3.3.3'));
        $this->assertTrue($this->comparator->isCompatible('3.3.3', '3.3.2'));
        $this->assertTrue($this->comparator->isCompatible('3.3.3', '3.2.4'));

        $this->assertFalse($this->comparator->isCompatible('3.3.3', '2.3.3'));
        $this->assertFalse($this->comparator->isCompatible('3.3.3', '3.4.3'));
        $this->assertFalse($this->comparator->isCompatible('3.3.3', '3.3.4'));
    }

    public function testThatVersionASatisfiesConstraint()
    {
        $this->assertTrue($this->comparator->satisfies('3.3.3', '^3.3.3'));
        $this->assertTrue($this->comparator->satisfies('3.3.3', '^3.2.3'));
        $this->assertTrue($this->comparator->satisfies('3.3.3', '3.3.3'));
        $this->assertFalse($this->comparator->satisfies('3.3.3', '3.2.3'));
    }

    public function testCaretOperatorLess010()
    {
        // Test für Versionen kleiner als 0.1.0
        $this->assertFalse($this->comparator->satisfies('0.0.9', '^0.0.0'));
        $this->assertFalse($this->comparator->satisfies('0.0.9', '^0.1.0'));
        $this->assertFalse($this->comparator->satisfies('0.0.9', '^1.0.0'));
        $this->assertTrue($this->comparator->satisfies('0.0.9', '^0.0.9'));
        $this->assertTrue($this->comparator->satisfies('0.0.9', '^0.0.9-beta.1'));
    }

    public function testCaretOperatorLess100()
    {
        // Test für Versionen kleiner als 1.0.0
        $this->assertTrue($this->comparator->satisfies('0.9.9', '^0.9.0'));
        $this->assertTrue($this->comparator->satisfies('0.9.9', '^0.9.9'));
        $this->assertFalse($this->comparator->satisfies('0.9.9', '^0.10.0'));
        $this->assertFalse($this->comparator->satisfies('0.9.9', '^1.0.0'));
        $this->assertFalse($this->comparator->satisfies('0.10.0', '^0.9.0'));
    }

    public function testCaretOperatorUntil100()
    {
        // Test für Versionen ab 1.0.0
        $this->assertTrue($this->comparator->satisfies('1.0.0', '^1.0.0'));
        $this->assertFalse($this->comparator->satisfies('1.0.0', '^1.0.1'));
        $this->assertTrue($this->comparator->satisfies('1.1.0', '^1.0.0'));
        $this->assertFalse($this->comparator->satisfies('2.0.0', '^1.0.0'));
    }


    public function testThatVersionASatisfiesOrConstraint()
    {
        $this->assertTrue($this->comparator->satisfies('3.3.3', '^2.2.2 || ^3.3.3'));
        $this->assertFalse($this->comparator->satisfies('4.4.4', '^2.2.2 || ^3.3.3'));
    }

    public function testThatVersionASatisfiesAndConstraint()
    {
        $this->assertFalse($this->comparator->satisfies('3.3.3', '^2.2.2, ^3.3.3'));
        $this->assertTrue($this->comparator->satisfies('4.4.4', '^4.4.2, ^4.4.3'));
    }
}
