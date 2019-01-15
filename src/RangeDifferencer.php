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
    public static function findDifferences3(
        RangeComparatorInterface $ancestor,
        RangeComparatorInterface $left,
        RangeComparatorInterface $right
    ): array {
        if (null === $ancestor) {
            return static::findDifferences($left, $right);
        }

        $leftAncestorScript = [];
        $rightAncestorScript = static::findDifferences($ancestor, $right);

        if (!empty($rightAncestorScript)) {
            $leftAncestorScript = static::findDifferences($ancestor, $left);
        }

        if (empty($rightAncestorScript) || empty($leftAncestorScript)) {
            return [];
        }

        $myIterator = new DifferencesIterator($rightAncestorScript);
        $yourIterator = new DifferencesIterator($leftAncestorScript);
        $diff3 = [];

        // Add a sentinel.
        $diff3[] = new RangeDifference(RangeDifference::ERROR);

        // Combine the two two-way edit scripts into one.
        while (null !== $myIterator->getDifference() || null !== $yourIterator->getDifference()) {
            $myIterator->removeAll();
            $yourIterator->removeAll();

            if (null === $myIterator->getDifference()) {
                $startThread = $yourIterator;
            } elseif (null === $yourIterator->getDifference()) {
                $startThread = $myIterator;
            } else {
                // Not at end of both scripts take the lowest range.
                if ($myIterator->getDifference()->getLeftStart() < $yourIterator->getDifference()->getLeftStart()) {
                    // 2 -> common (Ancestor)
                    $startThread = $myIterator;
                } elseif ($myIterator->getDifference()->getLeftStart() >
                    $yourIterator->getDifference()->getLeftStart()) {
                    $startThread = $yourIterator;
                } else {
                    if ($myIterator->getDifference()->getLeftLength() === 0 &&
                        $yourIterator->getDifference()->getLeftLength() === 0) {
                        // Insertion into the same position is conflict.
                        $changeRangeStart = $myIterator->getDifference()->getLeftStart();
                        $changeRangeEnd = $myIterator->getDifference()->getLeftEnd();

                        $myIterator->next();
                        $yourIterator->next();

                        $diff3[] = static::createRangeDifference3(
                            $myIterator, $yourIterator, $diff3, $right, $left, $changeRangeStart, $changeRangeEnd);
                        continue;
                    } elseif (0 === $myIterator->getDifference()->getLeftLength()) {
                        // Insertion into a position, and modification to the next line, is not conflict.
                        $startThread = $myIterator;
                    } elseif (0 === $yourIterator->getDifference()->getLeftLength()) {
                        $startThread = $yourIterator;
                    } else {
                        // Modifications to overlapping lines is conflict.
                        $startThread = $myIterator;
                    }
                }
            }

            $changeRangeStart = $startThread->getDifference()->getLeftStart();
            $changeRangeEnd = $startThread->getDifference()->getLeftEnd();
            $startThread->next();

            // Check for overlapping changes with other thread. Merge overlapping changes with this range.
            $other = $startThread->other($myIterator, $yourIterator);

            while (null !== $other->getDifference() && $other->getDifference()->getLeftStart() < $changeRangeEnd) {
                $newMax = $other->getDifference()->getLeftEnd();
                $other->next();

                if ($newMax > $changeRangeEnd) {
                    $changeRangeEnd = $newMax;
                    $other = $other->other($myIterator, $yourIterator);
                }
            }

            $diff3[] = static::createRangeDifference3(
                $myIterator, $yourIterator, $diff3, $right, $left, $changeRangeStart, $changeRangeEnd);
        }

        // Remove sentinel.
        array_shift($diff3);

        return $diff3;
    }

    /**
     * Finds the differences among two RangeComparatorInterfaces. In contrast to findDifferences, the result contains
     * RangeDifference elements for non-differing ranges too.
     *
     * @param RangeComparatorInterface $left
     * @param RangeComparatorInterface $right
     * @return RangeDifference[]
     */
    public static function findRanges(RangeComparatorInterface $left, RangeComparatorInterface $right): array
    {
        $in = static::findDifferences($left, $right);
        $out = [];

        $mStart = 0;
        $yStart = 0;

        for ($i = 0; $i < count($in); $i++) {
            $es = $in[$i];
            $rd = new RangeDifference(
                RangeDifference::NOCHANGE,
                $mStart, $es->getRightStart() - $mStart,
                $yStart, $es->getLeftStart() - $yStart);

            if (0 !== $rd->getMaxLength()) {
                $out[] = $rd;
            }

            $out[] = $es;

            $mStart = $es->getRightEnd();
            $yStart = $es->getLeftEnd();
        }

        $rd = new RangeDifference(RangeDifference::NOCHANGE,
            $mStart, $right->getRangeCount() - $mStart,
            $yStart, $left->getRangeCount() - $yStart);

        if ($rd->getMaxLength() > 0) {
            $out[] = $rd;
        }

        return $out;
    }

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
    public static function findRanges3(
        RangeComparatorInterface $ancestor,
        RangeComparatorInterface $left,
        RangeComparatorInterface $right): array
    {
        if (null === $ancestor) {
            return static::findRanges($left, $right);
        }

        $in = static::findDifferences3($ancestor, $left, $right);
        $out = [];

        $mStart = 0;
        $yStart = 0;
        $aStart = 0;

        for ($i = 0; $i < \count($in); $i++) {
            $es = $in[$i];
            $rd = new RangeDifference(RangeDifference::NOCHANGE,
                $mStart, $es->getRightStart() - $mStart,
                $yStart, $es->getLeftStart() - $yStart,
                $aStart, $es->getAncestorStart() - $aStart);

            if ($rd->getMaxLength() > 0) {
                $out[] = $rd;
            }

            $out[] = $es;

            $mStart = $es->getRightEnd();
            $yStart = $es->getLeftEnd();
            $aStart = $es->getAncestorEnd();
        }

        $rd = new RangeDifference(RangeDifference::NOCHANGE,
            $mStart, $right->getRangeCount() - $mStart,
            $yStart, $left->getRangeCount() - $yStart,
            $aStart, $ancestor->getRangeCount() - $aStart);

        if ($rd->getMaxLength() > 0) {
            $out[] = $rd;
        }

        return $out;
    }

    /**
     * @param DifferencesIterator      $myIterator
     * @param DifferencesIterator      $yourIterator
     * @param array                    $diff3
     * @param RangeComparatorInterface $right
     * @param RangeComparatorInterface $left
     * @param int                      $changeRightStart
     * @param int                      $changeRightEnd
     * @return RangeDifference
     */
    private static function createRangeDifference3(
        DifferencesIterator $myIterator,
        DifferencesIterator $yourIterator,
        array &$diff3,
        RangeComparatorInterface $right,
        RangeComparatorInterface $left,
        int $changeRightStart,
        int $changeRightEnd
    ): RangeDifference {
        $kind = RangeDifference::ERROR;
        /** @var RangeDifference $last */
        $last = $diff3[\count($diff3) - 1];

        // At least one range array must be non-empty.
        assert(true === ($myIterator->getCount() !== 0 || $yourIterator->getCount() !== 0));

        // Find corresponding lines to changeRangeStart/End in right and left.
        if ($myIterator->getCount() === 0) {
            // Only left changed.
            $rightStart = $changeRightStart - $last->getAncestorEnd() + $last->getRightEnd();
            $rightEnd = $changeRightEnd - $last->getAncestorEnd() + $last->getRightEnd();
            $kind = RangeDifference::LEFT;
        } else {
            $myRange = $myIterator->getRange();
            $f = $myRange[0];
            $l = $myRange[\count($myRange) - 1];
            $rightStart = $changeRightStart - $f->getLeftStart() + $f->getRightStart();
            $rightEnd = $changeRightEnd - $l->getLeftEnd() + $l->getRightEnd();
        }

        if ($yourIterator->getCount() === 0) {
            // Only right changed.
            $leftStart = $changeRightStart - $last->getAncestorEnd() + $last->getLeftEnd();
            $leftEnd = $changeRightEnd - $last->getAncestorEnd() + $last->getLeftEnd();
            $kind = RangeDifference::RIGHT;
        } else {
            $yourRange = $yourIterator->getRange();
            $f = $yourRange[0];
            $l = $yourRange[\count($yourRange) - 1];
            $leftStart = $changeRightStart - $f->getLeftStart() + $f->getRightStart();
            $leftEnd = $changeRightEnd - $l->getLeftEnd() + $l->getRightEnd();
        }

        if ($kind === RangeDifference::ERROR) {
            // Overlapping change (conflict) -> compare the changed ranges.
            if (static::rangeSpansEqual(
                $right, $rightStart, $rightEnd - $rightStart,
                $left, $leftStart, $leftEnd - $leftStart)) {
                $kind = RangeDifference::ANCESTOR;
            } else {
                $kind = RangeDifference::CONFLICT;
            }
        }

        return new RangeDifference(
            $kind,
            $rightStart, $rightEnd - $rightStart,
            $leftStart, $leftEnd - $leftStart,
            $changeRightStart, $changeRightEnd - $changeRightStart);
    }

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
    private static function rangeSpansEqual(
        RangeComparatorInterface $right,
        int $rightStart,
        int $rightLength,
        RangeComparatorInterface $left,
        int $leftStart,
        int $leftLength
    ): bool {
        if ($rightLength === $leftLength) {
            for ($i = 0; $i < $rightLength; $i++) {
                if (!static::rangesEqual(
                    $right, $rightStart + $i,
                    $left, $leftStart + $i)) {
                    break;
                }
            }

            if ($i === $rightLength) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tests if two ranges are equal.
     *
     * @param RangeComparatorInterface $a
     * @param int                      $ai
     * @param RangeComparatorInterface $b
     * @param int                      $bi
     * @return bool
     */
    private static function rangesEqual(
        RangeComparatorInterface $a,
        int $ai,
        RangeComparatorInterface $b,
        int $bi
    ): bool {
        return $a->rangesEqual($ai, $b, $bi);
    }
}
