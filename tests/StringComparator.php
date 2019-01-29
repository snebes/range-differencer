<?php
/**
 * (c) Steve Nebes <snebes@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SN\RangeDifferencer;

/**
 * A comparator that compares strings.
 *
 * @internal
 */
class StringComparator implements RangeComparatorInterface
{
    /** @var string[] */
    private $leafs = [];

    /**
     * Default values.
     *
     * @param string $text
     */
    public function __construct(string $text)
    {
        $split = \preg_split('/\s+/', $text);

        foreach ($split as $word) {
            $this->leafs[] = $word;
        }
    }

    /**
     * @param int $index
     * @return string
     *
     * @throws \OutOfRangeException
     */
    public function getLeaf(int $index): string
    {
        if (isset($this->lines[$index])) {
            return $this->leafs[$index];
        }

        throw new \OutOfRangeException();
    }

    /**
     * @return int
     */
    public function getRangeCount(): int
    {
        return \count($this->leafs);
    }

    /**
     * @param int                      $thisIndex
     * @param RangeComparatorInterface $other
     * @param int                      $otherIndex
     * @return bool
     */
    public function rangesEqual(int $thisIndex, RangeComparatorInterface $other, int $otherIndex): bool
    {
        if ($other instanceof StringComparator) {
            return $other->getLeaf($otherIndex) === $this->getLeaf($thisIndex);
        }

        return false;
    }

    /**
     * @param int                      $length
     * @param int                      $maxLength
     * @param RangeComparatorInterface $other
     * @return bool
     */
    public function skipRangeComparison(int $length, int $maxLength, RangeComparatorInterface $other): bool
    {
        return false;
    }
}
