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

class DocLineComparator implements TokenComparatorInterface
{
    /** @var string[] */
    private $document;

    /** @var string */
    private $documentStr = '';

    /** @var int */
    private $lineOffset = 0;

    /** @var int */
    private $lineCount = 0;

    /** @var int */
    private $length = 0;

    /** @var bool */
    private $ignoreWhiteSpace = false;

    /** @var array */
    private $compareFilters = [];

    /** @var string */
    private $contributor = '';

    public function __construct(string $document, bool $ignoreWhiteSpace, string $contributor = '?')
    {
        $this->document = \explode(PHP_EOL, $document);
        $this->documentStr = $document;
        $this->ignoreWhiteSpace = $ignoreWhiteSpace;
        $this->contributor = $contributor;

        $this->length = \mb_strlen($document);
        $this->lineCount = \count($this->document);

        for ($i = 0, $iMax = $this->lineCount - 1; $i < $iMax; $i++) {
//            $this->document[$i] .= PHP_EOL;
        }
    }

    public function getRangeCount(): int
    {
        return $this->lineCount;
    }

    public function getTokenStart(int $line): int
    {
        $line += $this->lineOffset;

        if (isset($this->document[$line])) {
            $offset = 0;

            for ($i = 0; $i < $line; $i++) {
                $offset += \mb_strlen($this->document[$i]);
            }

            return $offset;
        }

        return $this->length;
    }

    public function getTokenLength(int $index): int
    {
        return $this->getTokenStart($index + 1) - $this->getTokenStart($index);
    }

    public function rangesEqual(int $thisIndex, RangeComparatorInterface $other, int $otherIndex): bool
    {
        if (null !== $other && \get_class($this) === \get_class($other)) {
            if ($this->ignoreWhiteSpace) {
                $linesToCompare = $this->extract($thisIndex, $otherIndex, $other, false);

                return $this->compare($linesToCompare[0], $linesToCompare[1]);
            }

            $tLen = $this->getTokenLength($thisIndex);
            $oLen = $other->getTokenLength($otherIndex);

            if ($tLen === $oLen) {
                $linesToCompare = $this->extract($thisIndex, $otherIndex, $other, false);

                return $linesToCompare[0] === $linesToCompare[1];
            }
        }

        return false;
    }

    public function skipRangeComparison(int $length, int $maxLength, RangeComparatorInterface $other): bool
    {
        return false;
    }

    /**
     * @param int               $thisIndex
     * @param int               $otherIndex
     * @param DocLineComparator $other
     * @param bool              $includeSeparator
     * @return string[]
     */
    private function extract(int $thisIndex, int $otherIndex, DocLineComparator $other, bool $includeSeparator): array
    {
        $extracts = [
            $this->extract2($thisIndex, $includeSeparator),
            $other->extract2($otherIndex, $includeSeparator),
        ];

        return $extracts;
    }

    public function extract2(int $line, bool $includeSeparator): string
    {
        if ($line < $this->lineCount) {
            if ($includeSeparator) {
                return $this->document[$line];
            }

            return $this->document[$line];
        }

        return '';
    }

    private function compare(string $s1, string $s2): bool
    {
        $l1 = \mb_strlen($s1);
        $l2 = \mb_strlen($s2);
        $c1 = 0;
        $c2 = 0;
        $i1 = 0;
        $i2 = 0;

        while (-1 !== $c1) {
            $c1 = -1;

            while ($i1 < $l1) {
                $c = \mb_substr($s1, $i1++, 1);

                if (!in_array($c, [' ', "\n", "\r", "\t"], true)) {
                    $c1 = $c;
                    break;
                }
            }

            $c2 = -1;

            while ($i2 < $l2) {
                $c = \mb_substr($s2, $i2++, 1);

                if (!in_array($c, [' ', "\n", "\r", "\t"], true)) {
                    $c2 = $c;
                    break;
                }
            }

            if ($c1 !== $c2) {
                return false;
            }
        }

        return true;
    }
}
