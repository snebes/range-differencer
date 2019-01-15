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
 * An Atom that represents a piece of ordinary text.
 */
class TextAtom implements AtomInterface
{
    /** @var string */
    private $str;

    /**
     * @param string $str
     */
    public function __construct(string $str)
    {
        if (!$this->isValidAtom($str)) {
            throw new \InvalidArgumentException('The given String is not a valid Text Atom.');
        }

        $this->str = $str;
    }

    /** {@inheritdoc} */
    public function getFullText(): string
    {
        return $this->str;
    }

    /** {@inheritdoc} */
    public function getIdentifier(): string
    {
        return $this->str;
    }

    /** {@inheritdoc} */
    public function getInternalIdentifiers(): string
    {
        throw new \RuntimeException('This Atom has no internal identifiers.');
    }

    /** {@inheritdoc} */
    public function hasInternalIdentifiers(): bool
    {
        return false;
    }

    /** {@inheritdoc} */
    public function isValidAtom(string $str): bool
    {
        return \mb_strlen($str) > 0;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf('TextAtom: %s', $this->getFullText());
    }

    /** {@inheritdoc} */
    public function equalsIdentifier(AtomInterface $other): bool
    {
        return $other->getIdentifier() === $this->str;
    }
}
