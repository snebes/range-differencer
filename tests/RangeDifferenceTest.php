<?php

/**
 * (c) Steve Nebes <snebes@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace SN\RangeDifferencer;

use PHPUnit\Framework\TestCase;

class RangeDifferenceTest extends TestCase
{
    public function testGetters(): void
    {
        $diff = new RangeDifference(0, 1, 2, 3, 4, 5,6);

        $this->assertSame(0, $diff->getKind());
        $this->assertSame(1, $diff->getRightStart());
        $this->assertSame(2, $diff->getRightLength());
        $this->assertSame(3, $diff->getRightEnd());
        $this->assertSame(3, $diff->getLeftStart());
        $this->assertSame(4, $diff->getLeftLength());
        $this->assertSame(7, $diff->getLeftEnd());
        $this->assertSame(5, $diff->getAncestorStart());
        $this->assertSame(6, $diff->getAncestorLength());
        $this->assertSame(11, $diff->getAncestorEnd());
    }

    public function testGetMaxLength(): void
    {
        $diff = new RangeDifference(0, 1, 2, 3, 4, 5,6);

        $this->assertSame(6, $diff->getMaxLength());
    }

    public function testIsEqual(): void
    {
        $diff1 = new RangeDifference(0, 1, 2, 3, 4, 5,6);
        $diff2 = new RangeDifference(0, 1, 2, 3, 4, 5,6);
        $diff3 = new RangeDifference(1, 1, 2, 3, 4, 5,6);

        $this->assertTrue($diff1->isEqual($diff2));
        $this->assertFalse($diff1->isEqual($diff3));
    }

    public function testToString(): void
    {
        $diff = new RangeDifference(RangeDifference::NOCHANGE, 1, 2, 3, 4);
        $this->assertSame('RangeDifference {NOCHANGE, Left: (3, 4) Right: (1, 2)}', $diff->__toString());
        $diff = new RangeDifference(RangeDifference::CONFLICT, 1, 2, 3, 4, 5, 6);
        $this->assertSame('RangeDifference {CONFLICT, Left: (3, 4) Right: (1, 2) Ancestor: (5, 6)}', $diff->__toString());

        $diff = new RangeDifference(RangeDifference::CHANGE);
        $this->assertSame('RangeDifference {CHANGE/RIGHT, Left: (0, 0) Right: (0, 0)}', $diff->__toString());

        $diff = new RangeDifference(RangeDifference::LEFT);
        $this->assertSame('RangeDifference {LEFT, Left: (0, 0) Right: (0, 0)}', $diff->__toString());

        $diff = new RangeDifference(RangeDifference::ANCESTOR);
        $this->assertSame('RangeDifference {ANCESTOR, Left: (0, 0) Right: (0, 0)}', $diff->__toString());

        $diff = new RangeDifference(RangeDifference::ERROR);
        $this->assertSame('RangeDifference {ERROR, Left: (0, 0) Right: (0, 0)}', $diff->__toString());
    }

    public function testException(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        new RangeDifference(10);
    }
}
