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

class RangeComparatorLCSTest extends TestCase
{
    public function testDifferencesIterator()
    {
        $oldText = '<p> This is a blue book</p>';
        $newText = '<p> This is a <b>big</b> blue book</p>';

        $left = new TagComparator($oldText);
        $right = new TagComparator($newText);
        $rangeDifferences = RangeComparatorLCS::findDifferences($left, $right);

        $this->assertSame('Left: (8, 0) Right: (8, 4)', $rangeDifferences[0]->__toString());
    }
}
