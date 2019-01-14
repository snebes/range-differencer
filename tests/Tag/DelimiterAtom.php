<?php
/**
 * (c) Steve Nebes <snebes@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace SN\RangeDifferencer\Tag;

/**
 * A TextAtom with an identifier from a limited set of delimiter strings.
 */
class DelimiterAtom extends TextAtom
{
    /**
     * @param string $c
     */
    public function __construct(string $c)
    {
        parent::__construct($c);
    }

    /**
     * @param string $c
     * @return bool
     */
    public static function isValidDelimiter(string $c): bool
    {
        if (\mb_strlen($c) > 1) {
            return false;
        }

        switch ($c) {
            // Basic Delimiters.
            case '/':
            case '.':
            case '!':
            case ',':
            case ';':
            case '?':
            case ' ':
            case '=':
            case "'":
            case '"':
            case "\t":
            case "\r":
            case "\n":
            // Extra Delimiters.
            case '[':
            case ']':
            case '{':
            case '}':
            case '(':
            case ')':
            case '&':
            case '|':
            case "\\":
            case '-':
            case '_':
            case '+':
            case '*':
            case ':':
                return true;
        }

        return false;
    }

    /** {@inheritdoc} */
    public function isValidAtom(string $c): bool
    {
        return parent::isValidAtom($c) && $this->isValidDelimiterAtom($c);
    }

    /**
     * @param string $c
     * @return bool
     */
    private function isValidDelimiterAtom(string $c): bool
    {
        return self::isValidDelimiter($c);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $search  = ["\n", "\r", "\t"];
        $replace = ["\\\\n", "\\\\r", "\\\\t"];

        return \sprintf('DelimiterAtom: %s', \str_replace($search, $replace, $this->getFullText()));
    }

    /** {@inheritdoc} */
    public function equalsIdentifier(AtomInterface $other): bool
    {
        return
            parent::equalsIdentifier($other) ||
            (($other->getIdentifier() === '' || $other->getIdentifier() === "\n") &&
            ($this->getIdentifier() === '' || $this->getIdentifier() === "\n"));
    }
}
