<?php
/**
 * (c) Steve Nebes <snebes@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace SN\RangeDifferencer\Tag;

use InvalidArgumentException;

/**
 * An atom that represents a closing or opening tag.
 */
class TagAtom implements AtomInterface
{
    /** @var string */
    private $identifier = '';

    /** @var string */
    private $internalIdentifiers = '';

    /**
     * @param string $str
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $str)
    {
        if (!$this->isValidAtom($str)) {
            throw new InvalidArgumentException('The given string is not a valid tag.');
        }

        // Remove the < and >.
        $str = \mb_substr($str, 1, -1);

        if (false !== $pos = \mb_strpos($str, ' ')) {
            $this->identifier = \mb_substr($str, 0, $pos);
            $this->internalIdentifiers = mb_substr($str, $pos + 1);
        } else {
            $this->identifier = $str;
        }
    }

    /** {@inheritdoc} */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /** {@inheritdoc} */
    public function getInternalIdentifiers(): string
    {
        return $this->internalIdentifiers;
    }

    /** {@inheritdoc} */
    public function hasInternalIdentifiers(): bool
    {
        return \mb_strlen($this->internalIdentifiers) > 0;
    }

    /**
     * @param string $str
     * @return bool
     */
    public static function isValidTag(string $str): bool
    {
        return
            0 === \mb_strrpos($str, '<') &&
            \mb_strpos($str, '>') === \mb_strlen($str) - 1 &&
            \mb_strlen($str) >= 3;
    }

    /** {@inheritdoc} */
    public function getFullText(): string
    {
        $s = sprintf('<%s', $this->identifier);

        if ($this->hasInternalIdentifiers()) {
            $s .= sprintf(' %s', $this->internalIdentifiers);
        }

        return sprintf('%s>', $s);
    }

    /** {@inheritdoc} */
    public function isValidAtom(string $str): bool
    {
        return self::isValidTag($str);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf('TagAtom: %s', $this->getFullText());
    }

    /** {@inheritdoc} */
    public function equalsIdentifier(AtomInterface $other): bool
    {
        return $other->getIdentifier() === $this->identifier;
    }
}
