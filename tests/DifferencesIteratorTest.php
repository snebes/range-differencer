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
 * DifferencesIterator Tests
 */
class DifferencesIteratorTest extends TestCase
{
    /**
     * @return RangeDifference[]
     */
    private function getDifferences(): array
    {
        $oldText = '<p> This is a blue book</p>';
        $newText = '<p> This is a <b>big</b> blue book</p>';
        $oldComp = new TagComparator($oldText);
        $newComp = new TagComparator($newText);

        return RangeDifferencer::findDifferences($oldComp, $newComp);
    }

    public function testGetIndex(): void
    {
        $diff = $this->getDifferences();
        $iter = new DifferencesIterator($diff);

        $this->assertSame('RangeDifference {CHANGE/RIGHT, Left: (8, 0) Right: (8, 4)}', $diff[0]->__toString());
        $this->assertSame(1, $iter->getIndex());
    }

    public function testEmpty(): void
    {
        $iter = new DifferencesIterator([]);

        $this->assertSame(0, $iter->getIndex());
    }

    public function testGetCount(): void
    {
        $iter = new DifferencesIterator($this->getDifferences());

        $this->assertSame(0, $iter->getCount());
    }

    public function testGetCountEmpty(): void
    {
        $iter = new DifferencesIterator([]);

        $this->assertSame(0, $iter->getCount());
    }

    public function testNext(): void
    {
        $diff = $this->getDifferences();
        $iter = new DifferencesIterator($diff);

        $this->assertSame('RangeDifference {CHANGE/RIGHT, Left: (8, 0) Right: (8, 4)}', $diff[0]->__toString());

        $oldText = '<p> This is a green book about food</p>';
        $newText = '<p> This is a <b>big</b> blue book</p>';
        $oldComp = new TagComparator($oldText);
        $newComp = new TagComparator($newText);

        $iter->next();
        $diff = RangeDifferencer::findDifferences($oldComp, $newComp);
        $iter = new DifferencesIterator($diff);
        $iter->next();

        $this->assertSame('RangeDifference {CHANGE/RIGHT, Left: (8, 1) Right: (8, 5)}', $diff[0]->__toString());
    }

    public function testNextNull(): void
    {
        $diff = $this->getDifferences();
        $iter = new DifferencesIterator($diff);

        $this->assertSame('RangeDifference {CHANGE/RIGHT, Left: (8, 0) Right: (8, 4)}', $diff[0]->__toString());

        $iter->next();
        $iter = new DifferencesIterator([]);
        $iter->next();

        $this->assertSame(1, $iter->getCount());
    }

    public function testOther(): void
    {
        $diff = $this->getDifferences();
        $left = new DifferencesIterator($diff);
        $right = new DifferencesIterator($diff);

        $this->assertSame($right, $left->other($right, $left));
        $this->assertSame($left, $right->other($right, $left));
    }

    public function testRemoveAll(): void
    {
        $iter = new DifferencesIterator($this->getDifferences());
        $iter->removeAll();

        $this->assertSame(0, $iter->getCount());
    }

    public function testRemoveAllEmpty(): void
    {
        $iter = new DifferencesIterator([]);
        $iter->removeAll();

        $this->assertSame(0, $iter->getCount());
    }
}
