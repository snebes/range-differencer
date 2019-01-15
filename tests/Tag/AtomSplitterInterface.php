<?php
/**
 * (c) Steve Nebes <snebes@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace SN\RangeDifferencer\Tag;

use SN\RangeDifferencer\RangeComparatorInterface;

/**
 * Extens the IRangeComparator interface with functionality to recreate parts of the original document.
 */
interface AtomSplitterInterface extends RangeComparatorInterface
{
    /**
     * @param int $i
     * @return AtomInterface
     */
    public function getAtom(int $i): AtomInterface;

    /**
     * @param int $startAtom
     * @param int $endAtom
     * @return string
     */
    public function substring(int $startAtom, int $endAtom): string;
}