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
 * TextLineLCS
 */
class TextLineLCS extends AbstractLCS
{
    /** @var TextLine[] */
    private $lines1 = [];

    /** @var TextLine[] */
    private $lines2 = [];

    /** @var TextLine[][] */
    private $lcs = [];

    /**
     * Default values.
     *
     * @param TextLine[] $lines1
     * @param TextLine[] $lines2
     */
    public function __construct(array $lines1, array $lines2)
    {
        $this->lines1 = $lines1;
        $this->lines2 = $lines2;
    }

    /**
     * @return TextLine[][]
     */
    public function getResult(): array
    {
        $length = $this->getLength();
        $result = array_fill(0, 2, null);

        if (0 === $length) {
            return $result;
        }

        $result[0] = $this->compactAndShiftLCS($this->lcs[0], $length, $this->lines1);
        $result[1] = $this->compactAndShiftLCS($this->lcs[1], $length, $this->lines2);

        return $result;
    }

    /**
     * @return int
     */
    protected function getLength1(): int
    {
        return \count($this->lines1);
    }

    /**
     * @return int
     */
    protected function getLength2(): int
    {
        return \count($this->lines2);
    }

    /**
     * @param int $i1
     * @param int $i2
     * @return bool
     */
    protected function isRangeEqual(int $i1, int $i2): bool
    {
        return $this->lines1[$i1]->isSameText($this->lines2[$i2]);
    }

    /**
     * @param int $sl1
     * @param int $sl2
     */
    protected function setLcs(int $sl1, int $sl2): void
    {
        $this->lcs[0][$sl1] = $this->lines1[$sl1];
        $this->lcs[1][$sl1] = $this->lines2[$sl2];
    }

    /**
     * @param int $lcsLength
     */
    protected function initializeLcs(int $lcsLength): void
    {
        $this->lcs = array_fill(0, 2, array_fill(0, $lcsLength, null));
    }

    /**
     * @param TextLine[] $lcsSide
     * @param int        $len
     * @param TextLine[] $original
     * @return array
     */
    private function compactAndShiftLCS(array &$lcsSide, int $len, array &$original): array
    {
        /** @var TextLine[] $result */
        $result = array_fill(0, $len, null);

        if (0 === $len) {
            return $result;
        }

        $j = 0;

        while (null === $lcsSide[$j]) {
            $j++;
        }

        $result[0] = $lcsSide[$j];
        $j++;

        for ($i = 1; $i < $len; $i++) {
            while (null === $lcsSide[$j]) {
                $j++;
            }

            if ($original[$result[$i - 1]->getLineNumber() + 1]->isSameText($lcsSide[$j])) {
                $result[$i] = $original[$result[$i - 1]->getLineNumber() + 1];
            } else {
                $result[$i] = $lcsSide[$j];
            }

            $j++;
        }

        return $result;
    }

    /**
     * @param string $text
     * @return TextLine[]
     */
    public static function getTextLines(string $text): array
    {
        $lines = [];
        $begin = 0;
        $end = static::getEOL($text, 0);
        $lineNum = 0;

        while (-1 !== $end) {
            $lines[] = new TextLine($lineNum++, \mb_substr($text, $begin, $end - $begin));
            $begin = $end + 1;
            $end = static::getEOL($text, $begin);

            if ($end === $begin && "\r" === \mb_substr($text, $begin - 1, 1)  && "\n" === \mb_substr($text, $begin, 1)) {
                // We have \r followed by \n, skip it.
                $begin = $end + 1;
                $end = static::getEOL($text, $begin);
            }
        }

        $lines[] = new TextLine($lineNum, \mb_substr($text, $begin));

        return $lines;
    }

    /**
     * @param string $text
     * @param int    $start
     * @return int
     */
    private static function getEOL(string $text, int $start): int
    {
        $max = \mb_strlen($text);

        for ($i = $start; $i < $max; $i++) {
            $c = \mb_substr($text, $i, 1);

            if ("\n" === $c || "\r" === $c) {
                return $i;
            }
        }

        return -1;
    }
}
