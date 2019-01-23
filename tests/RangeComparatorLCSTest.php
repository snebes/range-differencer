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
use SN\RangeDifferencer\Core\LCS;
use SN\RangeDifferencer\Tag\TagComparator;

/**
 * RangeComparatorLCS Tests
 */
class RangeComparatorLCSTest extends TestCase
{
    public function testDifferencesIterator(): void
    {
        $oldText = '<p> This is a blue book</p>';
        $newText = '<p> This is a <b>big</b> blue book</p>';

        $left = new TagComparator($oldText);
        $right = new TagComparator($newText);
        $rangeDifferences = RangeComparatorLCS::findDifferences($left, $right);

        $this->assertSame('RangeDifference {CHANGE/RIGHT, Left: (8, 0) Right: (8, 4)}', $rangeDifferences[0]->__toString());
    }

    public function testLength(): void
    {
        $oldText = '<p> This is a blue book</p>';
        $newText = '<p> This is a <b>big</b> blue book</p>';
        $left = new TagComparator($oldText);
        $right = new TagComparator($newText);

        $comp = new RangeComparatorLCS($left, $right);
        $this->assertSame(0, $comp->getLength());

        $refMethod = new \ReflectionMethod($comp, 'getLength1');
        $refMethod->setAccessible(true);
        $this->assertSame(12, $refMethod->invoke($comp));

        $refMethod = new \ReflectionMethod($comp, 'getLength2');
        $refMethod->setAccessible(true);
        $this->assertSame(16, $refMethod->invoke($comp));
    }

    public function testLengthEmpty1(): void
    {
        $oldText = '';
        $newText = '<p> This is a <b>big</b> blue book</p>';
        $left = new TagComparator($oldText);
        $right = new TagComparator($newText);

        $comp = new RangeComparatorLCS($left, $right);
        $this->assertSame(0, $comp->getLength());

        $refMethod = new \ReflectionMethod($comp, 'getLength1');
        $refMethod->setAccessible(true);
        $this->assertSame(0, $refMethod->invoke($comp));

        $refMethod = new \ReflectionMethod($comp, 'getLength2');
        $refMethod->setAccessible(true);
        $this->assertSame(16, $refMethod->invoke($comp));
    }

    public function testLengthEmpty2(): void
    {
        $oldText = '<p> This is a blue book</p>';
        $newText = '';
        $left = new TagComparator($oldText);
        $right = new TagComparator($newText);

        $comp = new RangeComparatorLCS($left, $right);
        $this->assertSame(0, $comp->getLength());

        $refMethod = new \ReflectionMethod($comp, 'getLength1');
        $refMethod->setAccessible(true);
        $this->assertSame(12, $refMethod->invoke($comp));

        $refMethod = new \ReflectionMethod($comp, 'getLength2');
        $refMethod->setAccessible(true);
        $this->assertSame(0, $refMethod->invoke($comp));
    }

    public function testLengthEmpty3(): void
    {
        $oldText = '';
        $newText = '';
        $left = new TagComparator($oldText);
        $right = new TagComparator($newText);

        $comp = new RangeComparatorLCS($left, $right);
        $this->assertSame(0, $comp->getLength());

        $refMethod = new \ReflectionMethod($comp, 'getLength1');
        $refMethod->setAccessible(true);
        $this->assertSame(0, $refMethod->invoke($comp));

        $refMethod = new \ReflectionMethod($comp, 'getLength2');
        $refMethod->setAccessible(true);
        $this->assertSame(0, $refMethod->invoke($comp));
    }

    public function testInitializeLCS(): void
    {
        $oldText = '<p> This is a blue book</p>';
        $newText = '<p> This is a <b>big</b> blue book</p>';
        $left = new TagComparator($oldText);
        $right = new TagComparator($newText);
        $comp = new RangeComparatorLCS($left, $right);

        $refMethod = new \ReflectionMethod($comp, 'initializeLcs');
        $refMethod->setAccessible(true);
        $refMethod->invoke($comp, 20);

        $refProp = new \ReflectionProperty($comp, 'lcs');
        $refProp->setAccessible(true);
        $lcs = $refProp->getValue($comp);

        $this->assertSame(2, \count($lcs));
        $this->assertSame(20, \count($lcs[0]));
        $this->assertSame(20, \count($lcs[1]));
    }

    public function testInitializeLCSZero(): void
    {
        $oldText = '<p> This is a blue book</p>';
        $newText = '<p> This is a <b>big</b> blue book</p>';
        $left = new TagComparator($oldText);
        $right = new TagComparator($newText);
        $comp = new RangeComparatorLCS($left, $right);

        $refMethod = new \ReflectionMethod($comp, 'initializeLcs');
        $refMethod->setAccessible(true);
        $refMethod->invoke($comp, 0);

        $refProp = new \ReflectionProperty($comp, 'lcs');
        $refProp->setAccessible(true);
        $lcs = $refProp->getValue($comp);

        $this->assertSame(2, \count($lcs));
        $this->assertSame(0, \count($lcs[0]));
        $this->assertSame(0, \count($lcs[1]));
    }

    public function testIsRangeEqual(): void
    {
        $oldText = '<p> This is a blue book</p>';
        $newText = '<p> This is a <b>big</b> blue book</p>';
        $left = new TagComparator($oldText);
        $right = new TagComparator($newText);
        $comp = new RangeComparatorLCS($left, $right);

        $refMethod = new \ReflectionMethod($comp, 'isRangeEqual');
        $refMethod->setAccessible(true);

        $this->assertTrue($refMethod->invoke($comp, 0, 0));
        $this->assertFalse($refMethod->invoke($comp, 0, 3));
    }

    public function testGetDifferences1(): void
    {
        $oldText = "<p> This is a blue book</p> \n <div style=\"example\">This book is about food</div>";
        $newText = '<p> This is a <b>big</b> blue book</p>';
        $left = new TagComparator($oldText);
        $right = new TagComparator($newText);
        $comp = new RangeComparatorLCS($left, $right);

        $diffs = $comp->getDifferences();
        $this->assertSame(1, \count($diffs));
        $this->assertSame('RangeDifference {CHANGE/RIGHT, Left: (0, 26) Right: (0, 16)}', $diffs[0]->__toString());
    }

    public function testGetDifferences2(): void
    {
        $oldText = "<div style=\"example\">This book is about food</div>";
        $newText = '<p> This is a <b>big</b> blue book</p>';
        $left = new TagComparator($oldText);
        $right = new TagComparator($newText);
        $comp = new RangeComparatorLCS($left, $right);

        $diffs = $comp->getDifferences();
        $this->assertSame(1, \count($diffs));
        $this->assertSame('RangeDifference {CHANGE/RIGHT, Left: (0, 11) Right: (0, 16)}', $diffs[0]->__toString());
    }

    public function testGetDifferences3(): void
    {
        $newText = "<p> This is a <b>big</b> blue book</p>";
        $right = new TagComparator($newText);
        $comp = new RangeComparatorLCS($right, $right);

        $diffs = $comp->getDifferences();
        $this->assertSame(1, \count($diffs));
        $this->assertSame('RangeDifference {CHANGE/RIGHT, Left: (0, 16) Right: (0, 16)}', $diffs[0]->__toString());
    }

    public function testGetDifferences4(): void
    {
        $newText = '';
        $right = new TagComparator($newText);
        $comp = new RangeComparatorLCS($right, $right);

        $diffs = $comp->getDifferences();
        $this->assertSame(1, \count($diffs));
        $this->assertSame('RangeDifference {CHANGE/RIGHT, Left: (0, 0) Right: (0, 0)}', $diffs[0]->__toString());
    }

    public function testGetDifferences5(): void
    {
        $oldText = "<div style=\"example\">This book is about food</div>";
        $newText = '';
        $left = new TagComparator($oldText);
        $right = new TagComparator($newText);
        $comp = new RangeComparatorLCS($left, $right);

        $diffs = $comp->getDifferences();
        $this->assertSame(1, \count($diffs));
        $this->assertSame('RangeDifference {CHANGE/RIGHT, Left: (0, 11) Right: (0, 0)}', $diffs[0]->__toString());
    }

    public function testGetDifferences6(): void
    {
        $oldText = "<div style=\"example\">This book is about food</div>";
        $newText = '';
        $left = new TagComparator($oldText);
        $right = new TagComparator($newText);
        $comp = new RangeComparatorLCS($right, $left);

        $diffs = $comp->getDifferences();
        $this->assertSame(1, \count($diffs));
        $this->assertSame('RangeDifference {CHANGE/RIGHT, Left: (0, 0) Right: (0, 11)}', $diffs[0]->__toString());
    }

    public function testFindMostProgress(): void
    {
        $lcs = $this->createMock(LCS::class);

        $M = 7;
        $N = 2;
        $limit = 3;
        $V = [
            [0,0,1,1,2,2,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
            [0,0,0,0,0,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
        ];

        $refMethod = new \ReflectionMethod($lcs, 'findMostProgress');
        $refMethod->setAccessible(true);

        $actual = $refMethod->invokeArgs($lcs, [$M, $N, $limit, $V]);
        $this->assertSame([0, 4, 5], $actual);

        $actual = $refMethod->invokeArgs($lcs, [$M, $N, 4, $V]);
        $this->assertSame([0, 1, 8], $actual);
    }
}
