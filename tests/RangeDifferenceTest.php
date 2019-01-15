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

    public function testEmpty(): void
    {
        $diff = new RangeDifference(RangeDifference::LEFT);

        $this->assertSame(RangeDifference::LEFT, $diff->getKind());
        $this->assertSame(0, $diff->getRightStart());
        $this->assertSame(0, $diff->getRightLength());
        $this->assertSame(0, $diff->getRightEnd());
        $this->assertSame(0, $diff->getLeftStart());
        $this->assertSame(0, $diff->getLeftLength());
        $this->assertSame(0, $diff->getLeftEnd());
        $this->assertSame(0, $diff->getAncestorStart());
        $this->assertSame(0, $diff->getAncestorLength());
        $this->assertSame(0, $diff->getAncestorEnd());
    }

    public function testRight(): void
    {
        $diff = new RangeDifference(RangeDifference::RIGHT, 0, 12, 0, 16);

        $this->assertSame(RangeDifference::RIGHT, $diff->getKind());
        $this->assertSame(0, $diff->getRightStart());
        $this->assertSame(12, $diff->getRightLength());
        $this->assertSame(12, $diff->getRightEnd());
        $this->assertSame(0, $diff->getLeftStart());
        $this->assertSame(16, $diff->getLeftLength());
        $this->assertSame(16, $diff->getLeftEnd());
        $this->assertSame(0, $diff->getAncestorStart());
        $this->assertSame(0, $diff->getAncestorLength());
        $this->assertSame(0, $diff->getAncestorEnd());
    }

    public function testConflict(): void
    {
        $diff = new RangeDifference(RangeDifference::CONFLICT, 0, 12, 0, 16, 0, 0);

        $this->assertSame(RangeDifference::CONFLICT, $diff->getKind());
        $this->assertSame(0, $diff->getRightStart());
        $this->assertSame(12, $diff->getRightLength());
        $this->assertSame(12, $diff->getRightEnd());
        $this->assertSame(0, $diff->getLeftStart());
        $this->assertSame(16, $diff->getLeftLength());
        $this->assertSame(16, $diff->getLeftEnd());
        $this->assertSame(0, $diff->getAncestorStart());
        $this->assertSame(0, $diff->getAncestorLength());
        $this->assertSame(0, $diff->getAncestorEnd());
    }

    public function testAncestor(): void
    {
        $diff = new RangeDifference(RangeDifference::ANCESTOR);

        $this->assertSame(RangeDifference::ANCESTOR, $diff->getKind());
    }

    public function testNoChange(): void
    {
        $diff = new RangeDifference(RangeDifference::NOCHANGE, 0, 12, 0, 16, 10, 0);

        $this->assertSame(RangeDifference::NOCHANGE, $diff->getKind());
        $this->assertSame(10, $diff->getAncestorStart());
    }

    public function testChange(): void
    {
        $diff = new RangeDifference(RangeDifference::CHANGE, 0, 12, 0, 16, 10, 32);

        $this->assertSame(RangeDifference::CHANGE, $diff->getKind());
        $this->assertSame(32, $diff->getAncestorLength());
    }

    public function testError(): void
    {
        $diff = new RangeDifference(RangeDifference::ERROR, 0, 12, 0, 16, 10, 32);

        $this->assertSame(RangeDifference::ERROR, $diff->getKind());
        $this->assertSame(42, $diff->getAncestorEnd());
    }

    public function testLeft(): void
    {
        $diff = new RangeDifference(RangeDifference::LEFT, 0, 12, 0, 16, 10, 32);

        $this->assertSame(RangeDifference::LEFT, $diff->getKind());
    }

    public function testMaxLength(): void
    {
        $diff = new RangeDifference(RangeDifference::LEFT, 0, 12, 0, 16, 10, 32);

        $this->assertSame(32, $diff->getMaxLength());
    }

    public function testEquals(): void
    {
        $diff1 = new RangeDifference(RangeDifference::CHANGE, 0, 12, 0, 16, 10, 32);
        $diff2 = new RangeDifference(RangeDifference::CONFLICT, 1, 10, 2, 26, 10, 3);

        $this->assertFalse($diff1->isEqual($diff2));
        $this->assertTrue($diff1->isEqual($diff1));
    }
}
