<?php
/**
 * (c) Steve Nebes <snebes@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace SN\RangeDifferencer;

use SN\RangeDifferencer\Core\LCS;

/**
 * RangeComparator using Longest Common Subsequence.
 *
 * @internal
 */
class RangeComparatorLCS extends LCS
{
    /** @var RangeComparatorInterface */
    private $comparator1;

    /** @var RangeComparatorInterface */
    private $comparator2;

    /** @var int[][] */
    private $lcs;

    /**
     * Default values.
     *
     * @param RangeComparatorInterface $comparator1
     * @param RangeComparatorInterface $comparator2
     */
    public function __construct(RangeComparatorInterface $comparator1, RangeComparatorInterface $comparator2)
    {
        $this->comparator1 = $comparator1;
        $this->comparator2 = $comparator2;
    }

    /**
     * @param RangeComparatorInterface $left
     * @param RangeComparatorInterface $right
     * @return RangeDifference[]
     */
    public static function findDifferences(RangeComparatorInterface $left, RangeComparatorInterface $right): array
    {
        $lcs = new static($left, $right);
        $lcs->longestCommonSubsequence();

        return $lcs->getDifferences();
    }

    /**
     * @return int
     */
    protected function getLength1(): int
    {
        return $this->comparator1->getRangeCount();
    }

    /**
     * @return int
     */
    protected function getLength2(): int
    {
        return $this->comparator2->getRangeCount();
    }

    /**
     * @param int $lcsLength
     */
    protected function initializeLcs(int $lcsLength): void
    {
        $this->lcs = \array_fill(0, 2, \array_fill(0, $lcsLength, 0));
    }

    /**
     * @param int $i1
     * @param int $i2
     * @return bool
     */
    protected function isRangeEqual(int $i1, int $i2): bool
    {
        return $this->comparator1->rangesEqual($i1, $this->comparator2, $i2);
    }

    /**
     * @param int $sl1
     * @param int $sl2
     */
    protected function setLcs(int $sl1, int $sl2): void
    {
        $this->lcs[0][$sl1] = $sl1 + 1;
        $this->lcs[1][$sl1] = $sl2 + 1;
    }

    /**
     * @return RangeDifference[]
     */
    public function getDifferences(): array
    {
        $differences = [];
        $length = $this->getLength();

        if (0 === $length) {
            $differences[] = new RangeDifference(
                RangeDifference::CHANGE,
                0, $this->comparator2->getRangeCount(),
                0, $this->comparator1->getRangeCount());
        } else {
            $index1 = 0;
            $index2 = 0;
            $s1 = -1;
            $s2 = -1;

            while ($index1 < \count($this->lcs[0]) && $index2 < \count($this->lcs[1])) {
                // Move both LCS lists to the next occupied slot.
                while (0 === $l1 = $this->lcs[0][$index1]) {
                    $index1++;

                    if ($index1 >= \count($this->lcs[0])) {
                        break;
                    }
                }

                if ($index1 >= \count($this->lcs[0])) {
                    break;
                }

                while (0 === $l2 = $this->lcs[1][$index2]) {
                    $index2++;

                    if ($index2 >= \count($this->lcs[1])) {
                        break;
                    }
                }

                if ($index2 >= \count($this->lcs[1])) {
                    break;
                }

                // Convert the entry to an array index (see setLcs(int, int)).
                $end1 = $l1 - 1;
                $end2 = $l2 - 1;

                if (-1 === $s1 && (0 !== $end1 || 0 !== $end2)) {
                    // There is a diff at the beginning.
                    // TODO: We need to confirm that this is the proper order.
                    $differences[] = new RangeDifference(RangeDifference::CHANGE, 0, $end2, 0, $end1);
                } elseif ($end1 !== $s1 + 1 || $end2 !== $s2 + 1) {
                    // A diff was found on one of the sides.
                    $leftStart = $s1 + 1;
                    $leftLength = $end1 - $leftStart;
                    $rightStart = $s2 + 1;
                    $rightLength = $end2 - $rightStart;

                    // TODO: We need to confirm that this is the proper order.
                    $differences[] = new RangeDifference(
                        RangeDifference::CHANGE, $rightStart, $rightLength, $leftStart, $leftLength);
                }

                $s1 = $end1;
                $s2 = $end2;
                $index1++;
                $index2++;
            }

            if (-1 !== $s1 && ($s1 + 1 < $this->comparator1->getRangeCount() ||
                    $s2 + 1 < $this->comparator2->getRangeCount())) {
                $leftStart = $s1 < $this->comparator1->getRangeCount() ? $s1 + 1 : $s1;
                $rightStart = $s2 < $this->comparator2->getRangeCount() ? $s2 + 1 : $s2;

                // TODO: We need to confirm that this is the proper order.
                $differences[] = new RangeDifference(
                    RangeDifference::CHANGE,
                    $rightStart, $this->comparator2->getRangeCount() - $s2 + 1,
                    $leftStart, $this->comparator1->getRangeCount() - $s1 + 1);
            }
        }

        return $differences;
    }

    /**
     * {@inheritdoc}
     */
    public function longestCommonSubsequence(): void
    {
        parent::longestCommonSubsequence();

        // The LCS can be null if one of the sides is empty.
        if (null !== $this->lcs) {
            $this->compactAndShiftLCS($this->lcs[0], $this->getLength(), $this->comparator1);
            $this->compactAndShiftLCS($this->lcs[1], $this->getLength(), $this->comparator2);
        }
    }

    /**
     * This method takes an LCS result interspersed with zeros (i.e. empty slots  from the LCS algorithm), compacts it
     * and shifts the LCS chunks as far towards the front as possible. This tends to produce good results most of the
     * time.
     *
     * @param int[]                    $lcsSide
     * @param int                      $length
     * @param RangeComparatorInterface $comparator
     */
    private function compactAndShiftLCS(array &$lcsSide, int $length, RangeComparatorInterface $comparator): void
    {
        // If the LCS is empty, just return.
        if (0 === $length) {
            return;
        }

        // Skip any leading slots.
        $j = 0;

        while (0 === $lcsSide[$j]) {
            $j++;
        }

        // Put the first non-empty value in position 0.
        $lcsSide[0] = $lcsSide[$j];
        $j++;

        // Push all non-empty values down into the first N slots (where N is the length)
        for ($i = 1; $i < $length; $i++) {
            while (0 === $lcsSide[$j]) {
                $j++;
            }

            /*
             * Push the difference down as far as possible by comparing the line at the start of the diff with the line
             * and the end and adjusting if they are the same.
             */
            $nextLine = $lcsSide[$i - 1] + 1;

            if ($nextLine !== $lcsSide[$j] && $comparator->rangesEqual($nextLine - 1, $comparator, $lcsSide[$j] - 1)) {
                $lcsSide[$i] = $nextLine;
            } else {
                $lcsSide[$i] = $lcsSide[$j];
            }

            $j++;
        }

        // Zero all slots after the length.
        for ($i = $length, $iMax = \count($lcsSide); $i < $iMax; $i++) {
            $lcsSide[$i] = 0;
        }
    }
}
