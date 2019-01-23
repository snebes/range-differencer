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
use SN\RangeDifferencer\Core\DocLineComparator;
use SN\RangeDifferencer\Core\TextLine;
use SN\RangeDifferencer\Core\TextLineLCS;
use SN\RangeDifferencer\Tag\TextAtom;

/**
 * LCS Tests
 */
class DiffTest extends TestCase
{
    public function testLineAddition(): void
    {
        $s1 = 'abc' . PHP_EOL . 'def' . PHP_EOL . 'xyz';
        $s2 = 'abc' . PHP_EOL . 'def' . PHP_EOL . '123' . PHP_EOL . 'xyz';

        $l1 = TextLineLCS::getTextLines($s1);
        $l2 = TextLineLCS::getTextLines($s2);
        $lcs = new TextLineLCS($l1, $l2);
        $lcs->longestCommonSubsequence();

        /** @var TextLine[][] $result */
        $result = $lcs->getResult();

        $this->assertTrue(\count($result[0]) === \count($result[1]));
        $this->assertCount(3, $result[0]);

        for ($i = 0, $iMax = \count($result[0]); $i < $iMax; $i++) {
            $this->assertTrue($result[0][$i]->sameText($result[1][$i]));
        }

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

        for ($i = 0, $iMax = \count($result[0]); $i < $iMax; $i++) {
            $this->assertTrue($result[0][$i]->sameText($result[1][$i]));
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

        $this->assertTrue(\count($result[0]) === \count($result[1]));
        $this->assertCount(2, $result[0]);

        for ($i = 0, $iMax = \count($result[0]); $i < $iMax; $i++) {
            $this->assertTrue($result[0][$i]->sameText($result[1][$i]));
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

        $this->assertTrue(\count($result[0]) === \count($result[1]));
        $this->assertCount(2, $result[0]);

        for ($i = 0, $iMax = \count($result[0]); $i < $iMax; $i++) {
            $this->assertTrue($result[0][$i]->sameText($result[1][$i]));
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

        $this->assertTrue(\count($result[0]) === \count($result[1]));
        $this->assertCount(2, $result[0]);

        for ($i = 0, $iMax = \count($result[0]); $i < $iMax; $i++) {
            $this->assertTrue($result[0][$i]->sameText($result[1][$i]));
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

        $this->assertTrue(\count($result[0]) === \count($result[1]));
        $this->assertCount(2, $result[0]);

        for ($i = 0, $iMax = \count($result[0]); $i < $iMax; $i++) {
            $this->assertTrue($result[0][$i]->sameText($result[1][$i]));
        }

        $this->assertSame(1, $result[0][0]->getLineNumber());
        $this->assertSame(2, $result[0][1]->getLineNumber());
        $this->assertSame(0, $result[1][0]->getLineNumber());
        $this->assertSame(1, $result[1][1]->getLineNumber());
    }

    private function toRangeComparator(string $s): RangeComparatorInterface
    {
        return new DocLineComparator($s, true);
    }

    /**
     * @param string $s1
     * @param string $s2
     * @return RangeDifference[]
     */
    private function getDifferences(string $s1, string $s2): array
    {
        $comp1 = $this->toRangeComparator($s1);
        $comp2 = $this->toRangeComparator($s2);
        $differences = RangeDifferencer::findDifferences($comp1, $comp2);
        $oldDifferences = RangeDifferencer::findDifferences($comp1, $comp2);

        $this->assertTrue(\count($differences) === \count($oldDifferences));

        for ($i = 0, $iMax = \count($oldDifferences); $i < $iMax; $i++) {
            $this->assertEquals($oldDifferences[$i], $differences[$i]);
        }

        return $differences;
    }

    public function testDocAddition(): void
    {
        $s1 = 'abc' . PHP_EOL . 'def' . PHP_EOL . 'xyz';
        $s2 = 'abc' . PHP_EOL . 'def' . PHP_EOL . '123' . PHP_EOL . 'xyz';

        /** @var RangeDifference[] $result */
        $result = $this->getDifferences($s1, $s2);

        $this->assertCount(1, $result);
        $this->assertSame(2, $result[0]->getLeftStart());
        $this->assertSame(0, $result[0]->getLeftLength());
        $this->assertSame(2, $result[0]->getRightStart());
        $this->assertSame(1, $result[0]->getRightLength());
    }

    public function testDocDeletion(): void
    {
        $s1 = 'abc' . PHP_EOL . 'def' . PHP_EOL . '123' . PHP_EOL . 'xyz';
        $s2 = 'abc' . PHP_EOL . 'def' . PHP_EOL . 'xyz';

        /** @var RangeDifference[] $result */
        $result = $this->getDifferences($s1, $s2);

        $this->assertCount(1, $result);
        $this->assertSame(2, $result[0]->getLeftStart());
        $this->assertSame(1, $result[0]->getLeftLength());
        $this->assertSame(2, $result[0]->getRightStart());
        $this->assertSame(0, $result[0]->getRightLength());
    }

    public function testDocAppendStart(): void
    {
        $s1 = 'abc' . PHP_EOL . 'def';
        $s2 = '123' . PHP_EOL . 'abc' . PHP_EOL . 'def';

        /** @var RangeDifference[] $result */
        $result = $this->getDifferences($s1, $s2);

        $this->assertCount(1, $result);
        $this->assertSame(0, $result[0]->getLeftStart());
        $this->assertSame(0, $result[0]->getLeftLength());
        $this->assertSame(0, $result[0]->getRightStart());
        $this->assertSame(1, $result[0]->getRightLength());
    }

    public function testDocDeleteStart(): void
    {
        $s1 = '123' . PHP_EOL . 'abc' . PHP_EOL . 'def';
        $s2 = 'abc' . PHP_EOL . 'def';

        /** @var RangeDifference[] $result */
        $result = $this->getDifferences($s1, $s2);

        $this->assertCount(1, $result);
        $this->assertSame(0, $result[0]->getLeftStart());
        $this->assertSame(1, $result[0]->getLeftLength());
        $this->assertSame(0, $result[0]->getRightStart());
        $this->assertSame(0, $result[0]->getRightLength());
    }

    public function testDocAppendEnd(): void
    {
        $s1 = 'abc' . PHP_EOL . 'def';
        $s2 = 'abc' . PHP_EOL . 'def' . PHP_EOL . '123';

        /** @var RangeDifference[] $result */
        $result = $this->getDifferences($s1, $s2);

        $this->assertCount(1, $result);
//        $this->assertSame(2, $result[0]->getLeftStart());
//        $this->assertSame(0, $result[0]->getLeftLength());
//        $this->assertSame(2, $result[0]->getRightStart());
//        $this->assertSame(1, $result[0]->getRightLength());
    }

    public function testDocDeleteEnd(): void
    {
        $s1 = 'abc' . PHP_EOL . 'def' . PHP_EOL . '123';
        $s2 = 'abc' . PHP_EOL . 'def';

        /** @var RangeDifference[] $result */
        $result = $this->getDifferences($s1, $s2);

        $this->assertCount(1, $result);
//        $this->assertSame(2, $result[0]->getLeftStart());
//        $this->assertSame(1, $result[0]->getLeftLength());
//        $this->assertSame(2, $result[0]->getRightStart());
//        $this->assertSame(0, $result[0]->getRightLength());
    }
}
