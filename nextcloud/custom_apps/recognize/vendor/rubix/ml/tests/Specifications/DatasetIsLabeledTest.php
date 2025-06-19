<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Specifications;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsLabeled;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Specifications
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsLabeled
 * @internal
 */
class DatasetIsLabeledTest extends TestCase
{
    /**
     * @test
     * @dataProvider passesProvider
     *
     * @param DatasetIsLabeled $specification
     * @param bool $expected
     */
    public function passes(DatasetIsLabeled $specification, bool $expected) : void
    {
        $this->assertSame($expected, $specification->passes());
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function passesProvider() : Generator
    {
        (yield [DatasetIsLabeled::with(Labeled::quick([['swamp', 'island', 'black knight', 'counter spell']], ['win'])), \true]);
        (yield [DatasetIsLabeled::with(Unlabeled::quick([['swamp', 'island', 'black knight', 'counter spell']])), \false]);
    }
}
