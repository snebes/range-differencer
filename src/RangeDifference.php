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
 * Description of a change between two or three ranges of comparable entities.
 *
 * RangeDifference objects are the elements of a compare result returned from
 * the RangeDifferencer find* methods. Clients use these objects as they are
 * returned from the differencer. This class is not intended to be instantiated
 * outside of the Compare framework.
 *
 * Note: A range in the RangeDifference object is given as a start index and
 * length in terms of comparable entities. However, these entity indices and
 * counts are not necessarily character positions. For example, if an entity
 * represents a line in a document, the start index would be a line number and
 * the count would be in lines.
 */
class RangeDifference
{
    /**
     * @const int Two-way change constant indicating no change.
     */
    const NOCHANGE = 0;

    /**
     * @const int Three-way change constant indicating a change in both right
     *            and left.
     */
    const CONFLICT = 1;

    /**
     * @const int Two-way change constant indicating two-way change (same as
     *            RIGHT)
     */
    const CHANGE = 2;

    /**
     * @const int Three-way change constant indicating a change in right.
     */
    const RIGHT = 2;

    /**
     * @const int Three-way change constant indicating a change in left.
     */
    const LEFT = 3;

    /**
     * @const int Three-way change constant indicating the same change in both
     *            right and left, that is only the ancestor is different.
     */
    const ANCESTOR = 4;

    /**
     * @const int Indicates an unknown change kind.
     */
    const ERROR = 5;

    /**
     * @var int Kind of change.
     */
    private $kind = self::NOCHANGE;

    /** @var int */
    private $leftStart = 0;

    /** @var int */
    private $leftLength = 0;

    /** @var int */
    private $rightStart = 0;

    /** @var int */
    private $rightLength = 0;

    /** @var int */
    private $ancestorStart = 0;

    /** @var int */
    private $ancestorLength = 0;

    /**
     * Default values.
     *
     * @param int $kind
     * @param int $rightStart
     * @param int $rightLength
     * @param int $leftStart
     * @param int $leftLength
     * @param int $ancestorStart
     * @param int $ancestorLength
     *
     * @throws \UnexpectedValueException
     */
    public function __construct(
        int $kind,
        int $rightStart = 0,
        int $rightLength = 0,
        int $leftStart = 0,
        int $leftLength = 0,
        int $ancestorStart = 0,
        int $ancestorLength = 0
    )
    {
        if ($kind < 0 || $kind > 5) {
            throw new \UnexpectedValueException();
        }

        $this->kind = $kind;
        $this->rightStart = $rightStart;
        $this->rightLength = $rightLength;
        $this->leftStart = $leftStart;
        $this->leftLength = $leftLength;
        $this->ancestorStart = $ancestorStart;
        $this->ancestorLength = $ancestorLength;
    }

    /**
     * @return int
     */
    public function getKind(): int
    {
        return $this->kind;
    }

    /**
     * @return int
     */
    public function getLeftStart(): int
    {
        return $this->leftStart;
    }

    /**
     * @return int
     */
    public function getLeftLength(): int
    {
        return $this->leftLength;
    }

    /**
     * @return int
     */
    public function getLeftEnd(): int
    {
        return $this->leftStart + $this->leftLength;
    }

    /**
     * @return int
     */
    public function getRightStart(): int
    {
        return $this->rightStart;
    }

    /**
     * @return int
     */
    public function getRightLength(): int
    {
        return $this->rightLength;
    }

    /**
     * @return int
     */
    public function getRightEnd(): int
    {
        return $this->rightStart + $this->rightLength;
    }

    /**
     * @return int
     */
    public function getAncestorStart(): int
    {
        return $this->ancestorStart;
    }

    /**
     * @return int
     */
    public function getAncestorLength(): int
    {
        return $this->ancestorLength;
    }

    /**
     * @return int
     */
    public function getAncestorEnd(): int
    {
        return $this->ancestorStart + $this->ancestorLength;
    }

    /**
     * Returns the maximum number of entities in the left, right, and ancestor
     * sides of this range.
     *
     * @return int
     */
    public function getMaxLength(): int
    {
        return \max(
            $this->rightLength,
            $this->leftLength,
            $this->ancestorLength);
    }

    /**
     * Compare equality of RangeDifference objects.
     *
     * @param RangeDifference $other
     * @return bool
     */
    public function isEqual(RangeDifference $other): bool
    {
        return
            $this->kind === $other->getKind() &&
            $this->leftStart === $other->getLeftStart() &&
            $this->leftLength === $other->getLeftLength() &&
            $this->rightStart === $other->getRightStart() &&
            $this->rightLength === $other->getRightLength() &&
            $this->ancestorStart === $other->getAncestorStart() &&
            $this->ancestorLength === $other->getAncestorLength();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $buffer = 'RangeDifference {';

        switch ($this->kind) {
            case self::NOCHANGE:
                $buffer .= 'NOCHANGE';
                break;

            case self::CHANGE:
                $buffer .= 'CHANGE/RIGHT';
                break;

            case self::CONFLICT:
                $buffer .= 'CONFLICT';
                break;

            case self::LEFT:
                $buffer .= 'LEFT';
                break;

            case self::ANCESTOR:
                $buffer .= 'ANCESTOR';
                break;

            case self::ERROR:
            default:
                $buffer .= 'ERROR';
                break;
        }

        $buffer .= sprintf(', Left: (%d, %d) Right: (%d, %d)',
            $this->leftStart, $this->leftLength,
            $this->rightStart, $this->rightLength);

        if ($this->ancestorStart > 0 || $this->ancestorLength > 0) {
            $buffer .= sprintf(' Ancestor: (%d, %d)',
                $this->ancestorStart, $this->ancestorLength);
        }

        $buffer .= '}';

        return $buffer;
    }
}
