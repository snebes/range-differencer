<?php
/**
 * (c) Steve Nebes <snebes@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace SN\RangeDifferencer\Core;

use SN\RangeDifferencer\RangeComparatorInterface;

interface TokenComparatorInterface extends RangeComparatorInterface
{
    public function getTokenStart(int $index): int;
    public function getTokenLength(int $index): int;
}
