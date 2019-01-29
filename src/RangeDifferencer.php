<?php
/**
 * (c) Steve Nebes <snebes@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace SN\RangeDifferencer;

/**
 * A RangeDifferencer finds the differences between two or three RangeComparatorInterfaces.
 *
 * To use the differencer, clients provide an RangeComparatorInterface that breaks their input data into a sequence of
 * comparable entities. The differencer returns the differences among these sequences as an array of RangeDifference
 * objects (findDifferences methods). Every RangeDifference represents a single kind of difference and the
 * corresponding ranges of the underlying comparable entities in the left, right, and optionally ancestor sides.
 *
 * Alternatively, the findRanges methods not only return objects for the differing ranges but for non-differing ranges
 * too.
 *
 * The algorithm used is an objectified version of one described in: A File Comparison Program, by Webb Miller and
 * Eugene W. Myers, Software Practice and Experience, Vol. 15, Nov. 1985.
 */
final class RangeDifferencer
{
    /**
     * Prevent class instantiation.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Finds the differences between two RangeComparatorInterfaces. The differences are returned as an array of
     * RangeDifferences. If no differences are detected an empty array is returned.
     *
     * @param RangeComparatorInterface $left
     * @param RangeComparatorInterface $right
     * @return RangeDifference[]
     */
    public static function findDifferences(RangeComparatorInterface $left, RangeComparatorInterface $right): array
    {
        return RangeComparatorLCS::findDifferences($left, $right);
    }

    /**
     * Finds the differences among three RangeComparatorInterfaces. The differences are returned as a list of
     * RangeDifferences. If no differences are detected an empty list is returned. If the ancestor range comparator is
     * null, a two-way comparison is performed.
     *
     * @param RangeComparatorInterface $ancestor
     * @param RangeComparatorInterface $left
     * @param RangeComparatorInterface $right
     * @return RangeDifference[]
     */
//    public static function findDifferences3(
//        RangeComparatorInterface $ancestor,
//        RangeComparatorInterface $left,
//        RangeComparatorInterface $right
//    ): array {
//        $leftAncestorScript = [];
//        $rightAncestorScript = static::findDifferences($ancestor, $right);
//
//        if (!empty($rightAncestorScript)) {
//            $leftAncestorScript = static::findDifferences($ancestor, $left);
//        }
//
//        if (empty($rightAncestorScript) || empty($leftAncestorScript)) {
//            return [];
//        }
//
//        $myIter = new DifferencesIterator($rightAncestorScript);
//        $yourIter = new DifferencesIterator($leftAncestorScript);
//
//        // Add a sentinel.
//        $diff3 = [];
//        $diff3[] = new RangeDifference(RangeDifference::ERROR);
//
//        // Combine the two two-way edit scripts into one.
//        while (null !== $myIter->getDifference() || null !== $yourIter->getDifference()) {
//            $myIter->removeAll();
//            $yourIter->removeAll();
//
//            if (null === $myIter->getDifference()) {
//                $startThread = $yourIter;
//            } elseif (null === $yourIter->getDifference()) {
//                $startThread = $myIter;
//            } else {
//                // Not at end of both scripts take the lowest range.
//                if ($myIter->getDifference()->getLeftStart() < $yourIter->getDifference()->getLeftStart()) {
//                    // 2 -> common (Ancestor)
//                    $startThread = $myIter;
//                } elseif ($myIter->getDifference()->getLeftStart() > $yourIter->getDifference()->getLeftStart()) {
//                    $startThread = $yourIter;
//                } else {
//                    if ($myIter->getDifference()->getLeftLength() === 0 &&
//                        $yourIter->getDifference()->getLeftLength() === 0) {
//                        // Insertion into the same position is conflict.
//                        $changeRangeStart = $myIter->getDifference()->getLeftStart();
//                        $changeRangeEnd = $myIter->getDifference()->getLeftEnd();
//
//                        $myIter->next();
//                        $yourIter->next();
//
//                        $diff3[] = static::createRangeDifference3(
//                            $myIter, $yourIter, $diff3, $right, $left, $changeRangeStart, $changeRangeEnd);
//                        continue;
//                    } elseif (0 === $myIter->getDifference()->getLeftLength()) {
//                        // Insertion into a position, and modification to the next line, is not conflict.
//                        $startThread = $myIter;
//                    } elseif (0 === $yourIter->getDifference()->getLeftLength()) {
//                        $startThread = $yourIter;
//                    } else {
//                        // Modifications to overlapping lines is conflict.
//                        $startThread = $myIter;
//                    }
//                }
//            }
//
//            $changeRangeStart = $startThread->getDifference()->getLeftStart();
//            $changeRangeEnd = $startThread->getDifference()->getLeftEnd();
//            $startThread->next();
//
//            // Check for overlapping changes with other thread. Merge overlapping changes with this range.
//            $other = $startThread->other($myIter, $yourIter);
//
//            while (null !== $other->getDifference() && $other->getDifference()->getLeftStart() < $changeRangeEnd) {
//                $newMax = $other->getDifference()->getLeftEnd();
//                $other->next();
//
//                if ($newMax > $changeRangeEnd) {
//                    $changeRangeEnd = $newMax;
//                    $other = $other->other($myIter, $yourIter);
//                }
//            }
//
//            $diff3[] = static::createRangeDifference3(
//                $myIter, $yourIter, $diff3, $right, $left, $changeRangeStart, $changeRangeEnd);
//        }
//
//        // Remove sentinel.
//        \array_shift($diff3);
//
//        return $diff3;
//    }

    /**
     * Finds the differences among two RangeComparatorInterfaces. In contrast to findDifferences, the result contains
     * RangeDifference elements for non-differing ranges too.
     *
     * @param RangeComparatorInterface $left
     * @param RangeComparatorInterface $right
     * @return RangeDifference[]
     */
//    public static function findRanges(RangeComparatorInterface $left, RangeComparatorInterface $right): array
//    {
//        $in = static::findDifferences($left, $right);
//        $out = [];
//
//        $mStart = 0;
//        $yStart = 0;
//
//        for ($i = 0, $iMax = \count($in); $i < $iMax; $i++) {
//            $es = $in[$i];
//            $rd = new RangeDifference(
//                RangeDifference::NOCHANGE,
//                $mStart, $es->getRightStart() - $mStart,
//                $yStart, $es->getLeftStart() - $yStart);
//
//            if (0 !== $rd->getMaxLength()) {
//                $out[] = $rd;
//            }
//
//            $out[] = $es;
//
//            $mStart = $es->getRightEnd();
//            $yStart = $es->getLeftEnd();
//        }
//
//        $rd = new RangeDifference(RangeDifference::NOCHANGE,
//            $mStart, $right->getRangeCount() - $mStart,
//            $yStart, $left->getRangeCount() - $yStart);
//
//        if ($rd->getMaxLength() > 0) {
//            $out[] = $rd;
//        }
//
//        return $out;
//    }

    /**
     * Finds the differences among three RangeComparatorInterfaces. In contrast to findDifferences, the result contains
     * RangeDifference elements for non-differing ranges too. If the ancestor range comparator is null, a two-way
     * comparison is performed.
     *
     * @param RangeComparatorInterface $ancestor
     * @param RangeComparatorInterface $left
     * @param RangeComparatorInterface $right
     * @return RangeDifference[]
     */
//    public static function findRanges3(
//        RangeComparatorInterface $ancestor,
//        RangeComparatorInterface $left,
//        RangeComparatorInterface $right): array
//    {
//        $in = static::findDifferences3($ancestor, $left, $right);
//        $out = [];
//
//        $mStart = 0;
//        $yStart = 0;
//        $aStart = 0;
//
//        for ($i = 0, $iMax = \count($in); $i < $iMax; $i++) {
//            $es = $in[$i];
//            $rd = new RangeDifference(RangeDifference::NOCHANGE,
//                $mStart, $es->getRightStart() - $mStart,
//                $yStart, $es->getLeftStart() - $yStart,
//                $aStart, $es->getAncestorStart() - $aStart);
//
//            if ($rd->getMaxLength() > 0) {
//                $out[] = $rd;
//            }
//
//            $out[] = $es;
//
//            $mStart = $es->getRightEnd();
//            $yStart = $es->getLeftEnd();
//            $aStart = $es->getAncestorEnd();
//        }
//
//        $rd = new RangeDifference(RangeDifference::NOCHANGE,
//            $mStart, $right->getRangeCount() - $mStart,
//            $yStart, $left->getRangeCount() - $yStart,
//            $aStart, $ancestor->getRangeCount() - $aStart);
//
//        if ($rd->getMaxLength() > 0) {
//            $out[] = $rd;
//        }
//
//        return $out;
//    }

    /**
     * @param DifferencesIterator      $myIter
     * @param DifferencesIterator      $yourIter
     * @param array                    $diff3
     * @param RangeComparatorInterface $right
     * @param RangeComparatorInterface $left
     * @param int                      $changeRangeStart
     * @param int                      $changeRangeEnd
     * @return RangeDifference
     */
//    private static function createRangeDifference3(
//        DifferencesIterator $myIter,
//        DifferencesIterator $yourIter,
//        array &$diff3,
//        RangeComparatorInterface $right,
//        RangeComparatorInterface $left,
//        int $changeRangeStart,
//        int $changeRangeEnd
//    ): RangeDifference {
//        $kind = RangeDifference::ERROR;
//        /** @var RangeDifference $last */
//        $last = $diff3[\count($diff3) - 1];
//
//        // At least one range array must be non-empty.
//        assert(true === ($myIter->getCount() !== 0 || $yourIter->getCount() !== 0));
//
//        // Find corresponding lines to changeRangeStart/End in right and left.
//        if ($myIter->getCount() === 0) {
//            // Only left changed.
//            $rightStart = $changeRangeStart - $last->getAncestorEnd() + $last->getRightEnd();
//            $rightEnd = $changeRangeEnd - $last->getAncestorEnd() + $last->getRightEnd();
//            $kind = RangeDifference::LEFT;
//        } else {
//            $myRange = $myIter->getRange();
//            $f = $myRange[0];
//            $l = $myRange[\count($myRange) - 1];
//            $rightStart = $changeRangeStart - $f->getLeftStart() + $f->getRightStart();
//            $rightEnd = $changeRangeEnd - $l->getLeftEnd() + $l->getRightEnd();
//        }
//
//        if ($yourIter->getCount() === 0) {
//            // Only right changed.
//            $leftStart = $changeRangeStart - $last->getAncestorEnd() + $last->getLeftEnd();
//            $leftEnd = $changeRangeEnd - $last->getAncestorEnd() + $last->getLeftEnd();
//            $kind = RangeDifference::RIGHT;
//        } else {
//            $yourRange = $yourIter->getRange();
//            $f = $yourRange[0];
//            $l = $yourRange[\count($yourRange) - 1];
//            $leftStart = $changeRangeStart - $f->getLeftStart() + $f->getRightStart();
//            $leftEnd = $changeRangeEnd - $l->getLeftEnd() + $l->getRightEnd();
//        }
//
//        if ($kind === RangeDifference::ERROR) {
//            // Overlapping change (conflict) -> compare the changed ranges.
//            if (static::rangeSpansEqual(
//                $right, $rightStart, $rightEnd - $rightStart,
//                $left, $leftStart, $leftEnd - $leftStart)) {
//                $kind = RangeDifference::ANCESTOR;
//            } else {
//                $kind = RangeDifference::CONFLICT;
//            }
//        }
//
//        return new RangeDifference(
//            $kind,
//            $rightStart, $rightEnd - $rightStart,
//            $leftStart, $leftEnd - $leftStart,
//            $changeRangeStart, $changeRangeEnd - $changeRangeStart);
//    }

    /**
     * Tests whether right and left changed in the same way.
     *
     * @param RangeComparatorInterface $right
     * @param int                      $rightStart
     * @param int                      $rightLength
     * @param RangeComparatorInterface $left
     * @param int                      $leftStart
     * @param int                      $leftLength
     * @return bool
     */
//    private static function rangeSpansEqual(
//        RangeComparatorInterface $right,
//        int $rightStart,
//        int $rightLength,
//        RangeComparatorInterface $left,
//        int $leftStart,
//        int $leftLength
//    ): bool {
//        if ($rightLength === $leftLength) {
//            for ($i = 0; $i < $rightLength; $i++) {
//                if (!static::rangesEqual($right, $rightStart + $i, $left, $leftStart + $i)) {
//                    break;
//                }
//            }
//
//            if ($i === $rightLength) {
//                return true;
//            }
//        }
//
//        return false;
//    }

    /**
     * Tests if two ranges are equal.
     *
     * @param RangeComparatorInterface $a
     * @param int                      $ai
     * @param RangeComparatorInterface $b
     * @param int                      $bi
     * @return bool
     */
//    private static function rangesEqual(
//        RangeComparatorInterface $a,
//        int $ai,
//        RangeComparatorInterface $b,
//        int $bi
//    ): bool {
//        return $a->rangesEqual($ai, $b, $bi);
//    }
}
