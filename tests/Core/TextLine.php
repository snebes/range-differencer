<?php
/**
 * (c) Steve Nebes <snebes@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace SN\RangeDifferencer\Core;

/**
 * @internal
 */
class TextLine
{
    /** @var int */
    private $lineNumber = 0;

    /** @var string */
    private $text = '';

    /**
     * Default values.
     *
     * @param int    $lineNumber
     * @param string $text
     */
    public function __construct(int $lineNumber, string $text)
    {
        $this->lineNumber = $lineNumber;
        $this->text = $text;
    }

    /**
     * @return int
     */
    public function getLineNumber(): int
    {
        return $this->lineNumber;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param TextLine $textLine
     * @return bool
     */
    public function sameText(TextLine $textLine): bool
    {
        return $this->text === $textLine->getText();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf("%d %s\n", $this->lineNumber, $this->text);
    }
}
