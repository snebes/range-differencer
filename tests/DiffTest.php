<?php
/**
 * (c) Steve Nebes <snebes@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace SN\RangeDifferencer;

use PHPUnit\Framework\TestCase;

class DiffTest extends TestCase
{
    public function testLineAdditions(): void
    {
        $s1 = 'abc' . PHP_EOL . 'def' . PHP_EOL . 'xyz';
        $s2 = 'abc' . PHP_EOL . 'def' . PHP_EOL . '123' . PHP_EOL . 'xyz';

        $l1 = TextLineLCS::getTextLines($s1);
        $l2 = TextLineLCS::getTextLines($s2);
        $lcs = new TextLineLCS($l1, $l2);
        $lcs->longestCommonSubsequence();

        /** @var TextLine[][] $result */
        $result = $lcs->getResult();

        $this->assertSame(0, $result[0][0]->getLineNumber());
        $this->assertSame(0, $result[1][0]->getLineNumber());
        $this->assertSame(1, $result[0][1]->getLineNumber());
        $this->assertSame(1, $result[1][1]->getLineNumber());
        $this->assertSame(2, $result[0][2]->getLineNumber());
        $this->assertSame(3, $result[1][2]->getLineNumber());
    }

    public function testLineDeletion(): void
    {
        $s1 = 'abc' . PHP_EOL . 'def' . PHP_EOL . '123' . PHP_EOL . 'xyz';
        $s2 = 'abc' . PHP_EOL . 'def' . PHP_EOL . 'xyz';

        $l1 = TextLineLCS::getTextLines($s1);
        $l2 = TextLineLCS::getTextLines($s2);
        $lcs = new TextLineLCS($l1, $l2);
        $lcs->longestCommonSubsequence();

        /** @var TextLine[][] $result */
        $result = $lcs->getResult();

        $this->assertTrue(\count($result[0]) === \count($result[1]));

        for ($i = 0; $i < \count($result[0]); $i++) {
            $this->assertTrue($result[0][$i]->isSameText($result[1][$i]));
        }

        $this->assertSame(0, $result[0][0]->getLineNumber());
        $this->assertSame(1, $result[0][1]->getLineNumber());
        $this->assertSame(3, $result[0][2]->getLineNumber());
        $this->assertSame(0, $result[1][0]->getLineNumber());
        $this->assertSame(1, $result[1][1]->getLineNumber());
        $this->assertSame(2, $result[1][2]->getLineNumber());
    }

    public function testLineAppendEnd(): void
    {
        $s1 = 'abc' . PHP_EOL . 'def';
        $s2 = 'abc' . PHP_EOL . 'def' . PHP_EOL . '123';

        $l1 = TextLineLCS::getTextLines($s1);
        $l2 = TextLineLCS::getTextLines($s2);
        $lcs = new TextLineLCS($l1, $l2);
        $lcs->longestCommonSubsequence();

        /** @var TextLine[][] $result */
        $result = $lcs->getResult();

        $this->assertSame(2, \count($result[0]));
        $this->assertSame(2, \count($result[1]));

        for ($i = 0; $i < \count($result[0]); $i++) {
            $this->assertTrue($result[0][$i]->isSameText($result[1][$i]));
        }

        $this->assertSame(0, $result[0][0]->getLineNumber());
        $this->assertSame(0, $result[1][0]->getLineNumber());
        $this->assertSame(1, $result[0][1]->getLineNumber());
        $this->assertSame(1, $result[1][1]->getLineNumber());
    }

    public function testLineDeleteEnd(): void
    {
        $s1 = 'abc' . PHP_EOL . 'def' . PHP_EOL . '123';
        $s2 = 'abc' . PHP_EOL . 'def';

        $l1 = TextLineLCS::getTextLines($s1);
        $l2 = TextLineLCS::getTextLines($s2);
        $lcs = new TextLineLCS($l1, $l2);
        $lcs->longestCommonSubsequence();

        /** @var TextLine[][] $result */
        $result = $lcs->getResult();

        $this->assertSame(2, \count($result[0]));
        $this->assertSame(2, \count($result[1]));

        for ($i = 0; $i < \count($result[0]); $i++) {
            $this->assertTrue($result[0][$i]->isSameText($result[1][$i]));
        }

        $this->assertSame(0, $result[0][0]->getLineNumber());
        $this->assertSame(0, $result[1][0]->getLineNumber());
        $this->assertSame(1, $result[0][1]->getLineNumber());
        $this->assertSame(1, $result[1][1]->getLineNumber());
    }

    public function testLineAppendStart(): void
    {
        $s1 = 'abc' . PHP_EOL . 'def';
        $s2 = '123' . PHP_EOL . 'abc' . PHP_EOL . 'def';

        $l1 = TextLineLCS::getTextLines($s1);
        $l2 = TextLineLCS::getTextLines($s2);
        $lcs = new TextLineLCS($l1, $l2);
        $lcs->longestCommonSubsequence();

        /** @var TextLine[][] $result */
        $result = $lcs->getResult();

        $this->assertSame(2, \count($result[0]));
        $this->assertSame(2, \count($result[1]));

        for ($i = 0; $i < \count($result[0]); $i++) {
            $this->assertTrue($result[0][$i]->isSameText($result[1][$i]));
        }

        $this->assertSame(0, $result[0][0]->getLineNumber());
        $this->assertSame(1, $result[1][0]->getLineNumber());
        $this->assertSame(1, $result[0][1]->getLineNumber());
        $this->assertSame(2, $result[1][1]->getLineNumber());
    }

    public function testLineDeleteStart(): void
    {
        $s1 = '123' . PHP_EOL . 'abc' . PHP_EOL . 'def';
        $s2 = 'abc' . PHP_EOL . 'def';

        $l1 = TextLineLCS::getTextLines($s1);
        $l2 = TextLineLCS::getTextLines($s2);
        $lcs = new TextLineLCS($l1, $l2);
        $lcs->longestCommonSubsequence();

        /** @var TextLine[][] $result */
        $result = $lcs->getResult();

        $this->assertSame(2, \count($result[0]));
        $this->assertSame(2, \count($result[1]));

        for ($i = 0; $i < \count($result[0]); $i++) {
            $this->assertTrue($result[0][$i]->isSameText($result[1][$i]));
        }

        $this->assertSame(1, $result[0][0]->getLineNumber());
        $this->assertSame(0, $result[1][0]->getLineNumber());
        $this->assertSame(2, $result[0][1]->getLineNumber());
        $this->assertSame(1, $result[1][1]->getLineNumber());
    }
}
