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
    public function testFindDifferencesExample1(): void
    {
        $oldText = '<p> This is a green book about food</p>';
        $newText = '<p> This is a <b>big</b> blue book</p>';
        $left = new TagComparator($oldText);
        $right = new TagComparator($newText);

        $diff = RangeDifferencer::findDifferences($left, $right);
    }
}
