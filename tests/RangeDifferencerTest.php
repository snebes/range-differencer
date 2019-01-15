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
use SN\RangeDifferencer\Tag\TagComparator;

/**
 * RangeDifferencer Tests
 */
class RangeDifferencerTest extends TestCase
{
    public function testFindDifferences_1(): void
    {
        $oldText = '<p> This is a green book about food</p>';
        $newText = '<p> This is a <b>big</b> blue book</p>';
        $left = new TagComparator($oldText);
        $right = new TagComparator($newText);

        $diffs = RangeDifferencer::findDifferences($left, $right);

        $this->assertSame(2, \count($diffs));
        $this->assertSame('RangeDifference {CHANGE/RIGHT, Left: (8, 1) Right: (8, 5)}', $diffs[0]->__toString());
        $this->assertSame('RangeDifference {CHANGE/RIGHT, Left: (11, 4) Right: (15, 0)}', $diffs[1]->__toString());
    }

    public function testFindDifferences_2(): void
    {
        $oldText = '<p> This is a <b>big</b> blue book</p>';
        $newText = '<p> This is a <b>big</b> blue book</p>';
        $left = new TagComparator($oldText);
        $right = new TagComparator($newText);

        $diffs = RangeDifferencer::findDifferences($left, $right);

        $this->assertSame(0, \count($diffs));
    }

    public function testFindRanges_1(): void
    {
        $oldText = '<p> This is a green book about food</p>';
        $newText = '<p> This is a <b>big</b> blue book</p>';
        $left = new TagComparator($oldText);
        $right = new TagComparator($newText);

        $diffs = RangeDifferencer::findRanges($left, $right);

        $this->assertSame(5, \count($diffs));
        $this->assertSame('RangeDifference {NOCHANGE, Left: (0, 8) Right: (0, 8)}', $diffs[0]->__toString());
        $this->assertSame('RangeDifference {CHANGE/RIGHT, Left: (8, 1) Right: (8, 5)}', $diffs[1]->__toString());
        $this->assertSame('RangeDifference {NOCHANGE, Left: (9, 2) Right: (13, 2)}', $diffs[2]->__toString());
        $this->assertSame('RangeDifference {CHANGE/RIGHT, Left: (11, 4) Right: (15, 0)}', $diffs[3]->__toString());
        $this->assertSame('RangeDifference {NOCHANGE, Left: (15, 1) Right: (15, 1)}', $diffs[4]->__toString());
    }

    public function testFindRanges3(): void
    {
        $ancestorText = '<p> This is a book </p>';
        $oldText = '<p> This is a green book about food</p>';
        $newText = '<p> This is a <b>big</b> blue book</p>';
        $ancestor = new TagComparator($ancestorText);
        $left = new TagComparator($oldText);
        $right = new TagComparator($newText);

        $diffs = RangeDifferencer::findDifferences3($ancestor, $left, $right);

        $this->assertSame(3, \count($diffs));
        $this->assertSame('RangeDifference {CONFLICT, Left: (8, 2) Right: (8, 6) Ancestor: (8, 0)}', $diffs[0]->__toString());
        $this->assertSame('RangeDifference {CHANGE/RIGHT, Left: (11, 1) Right: (15, 0) Ancestor: (9, 1)}', $diffs[1]->__toString());
        $this->assertSame('RangeDifference {LEFT, Left: (12, 3) Right: (15, 0) Ancestor: (10, 0)}', $diffs[2]->__toString());
    }
}
