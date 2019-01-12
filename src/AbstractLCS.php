<?php

/**
 * (c) Steve Nebes <snebes@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace SN\RangeDifferencer;

abstract class AbstractLCS
{
    /** @const float 10^8, the value of N*M when to start bindnig the run time. */
    private const TOO_LONG = 100000000.0;

    /** @const float Limit the time to D^POW_LIMIT */
    private const POW_LIMIT = 1.5;

    /** @var int */
    private $maxDifferences = 0;

    /** @var int */
    private $length = 0;

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * Myers' algorithm for longest common subsequence. O((M + N)D) worst case time, O(M + N + D^2) expected time, O(M
     * + N) space (http://citeseer.ist.psu.edu/myers86ond.html)
     *
     * Note: Beyond implementing the algorithm as described in the paper I have added diagonal range compression which
     * helps when finding the LCS of a very long and a very short sequence, also bound the running time to (N + M)^1.5
     * when both sequences are very long.
     *
     * After this method is called, the longest common subsequence is available by calling getResult() where result[0]
     * is composed of entries from l1 and result[1] is composed of entries from l2
     */
    public function longestCommonSubsequence(): void
    {
        $length1 = $this->getLength1();
        $length2 = $this->getLength2();

        if (0 === $length1 || 0 === $length2) {
            $this->length = 0;
        }

        $this->maxDifferences = (int)\ceil(($length1 + $length2 + 1) / 2);

        if ($length1 * $length2 > self::TOO_LONG) {
            // Limit complexity to D^POW_LIMIT for long sequences.
            $this->maxDifferences = (int)\pow($this->maxDifferences, self::POW_LIMIT - 1.0);
        }

        $this->initializeLcs($length1);

        // The common prefixes and suffixes are always part of some LCS, include them now to reduce our search space.
        $max = \min($length1, $length2);

        for (
            $forwardBound = 0;
            $forwardBound < $max && $this->isRangeEqual($forwardBound, $forwardBound);
            $forwardBound++
        ) {
            $this->setLcs($forwardBound, $forwardBound);
        }

        $backBoundL1 = $length1 - 1;
        $backBoundL2 = $length2 - 1;

        while ($backBoundL1 >= $forwardBound && $backBoundL2 >= $forwardBound &&
            $this->isRangeEqual($backBoundL1, $backBoundL2)) {
            $this->setLcs($backBoundL1, $backBoundL2);
            $backBoundL1--;
            $backBoundL2--;
        }

        $V = array_fill(0, 2, array_fill(0, $length1 + $length2 + 1, 0));
        $snake = array_fill(0, 3, 0);
        $lcsRec = $this->lcsRec($forwardBound, $backBoundL1, $forwardBound, $backBoundL2, $V, $snake);

        $this->length = $forwardBound + $length1 - $backBoundL1 - 1 + $lcsRec;
    }

    /**
     * The recursive helper function for Myers' LCS. Computes the LCS of $l1[$bottomL1 .. $topL1] and $l2[$bottomL2 ..
     * $topL2] fills in the appropriate location in lcs and returns the length.
     *
     * @param int   $bottomL1 The first sequence
     * @param int   $topL1    Index in the first sequence to start from (inclusive)
     * @param int   $bottomL2 The second sequence
     * @param int   $topL2    Index in the second sequence to start from (inclusive)
     * @param array $V        Furthest reaching D-paths.
     * @param array $snake    Used to store the beginning x, y coordinates and the length of the latest snake traversed
     * @return int Length of the lcs
     */
    private function lcsRec(int $bottomL1, int $topL1, int $bottomL2, int $topL2, array &$V, array &$snake): int
    {
        // Check that both sequences are non-empty.
        if ($bottomL1 > $topL1 || $bottomL2 > $topL2) {
            return 0;
        }

        $d = $this->findMiddleSnake($bottomL1, $topL1, $bottomL2, $topL2, $V, $snake);

        // Need to restore these so we don't lose them when they're overwritten by the recursion.
        $len = $snake[2];
        $startX = $snake[0];
        $startY = $snake[1];

        // The middle snake is part of the LCS, store it.
        for ($i = 0; $i < $len; $i++) {
            $this->setLcs($startX + $i, $startY + $i);
        }

        if ($d > 1) {
            return $len +
                $this->lcsRec($bottomL1, $startX - 1, $bottomL2, $startY - 1, $V, $snake) +
                $this->lcsRec($startX + $len, $topL1, $startY + $len, $topL2, $V, $snake);
        } elseif ($d === 1) {
            // In this case the sequences differ by exactly 1 line. We have already saved all the lines after the
            // difference in the for loop above, now we need to save all the lines before the difference.
            $max = \min($startX - $bottomL1, $startY - $bottomL2);

            for ($i = 0; $i < $max; $i++) {
                $this->setLcs($bottomL1 + $i, $bottomL2 + $i);
            }

            return $max + $len;
        }

        return $len;
    }

    private function findMiddleSnake(
        int $bottomL1,
        int $topL1,
        int $bottomL2,
        int $topL2,
        array &$V,
        array &$snake
    ): int {
        $N = $topL1 - $bottomL1 + 1;
        $M = $topL2 - $bottomL2 + 1;

        $delta = $N - $M;
        $isEven = ($delta & 1) === 1 ? false : true;

        $limit = \min($this->maxDifferences, (int)ceil(($N + $M + 1) / 2));

        // Offset to make it odd/even.
        // a 0 or 1 that we add to the start offset to make it odd/even
        $valueToAddForward = ($M & 1) == 1 ? 1 : 0;
        $valueToAddBackward = ($N & 1) == 1 ? 1 : 0;

        $startForward = -$M;
        $endForward = $N;
        $startBackward = -$N;
        $endBackward = $M;

        $V[0][$limit + 1] = 0;
        $V[0][$limit - 1] = $N;

        for ($d = 0; $d <= $limit; $d++) {
            
        }
    }

    /**
     * @return int
     */
    abstract protected function getLength1(): int;

    /**
     * @return int
     */
    abstract protected function getLength2(): int;

    /**
     * @param int $i1
     * @param int $i2
     * @return bool
     */
    abstract protected function isRangeEqual(int $i1, int $i2): bool;

    /**
     * @param int $lcsLength
     */
    abstract protected function initializeLcs(int $lcsLength): void;

    /**
     * @param int $sl1
     * @param int $sl2
     */
    abstract protected function setLcs(int $sl1, int $sl2): void;
}
