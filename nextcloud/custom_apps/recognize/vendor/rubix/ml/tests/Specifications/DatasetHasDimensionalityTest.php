<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Specifications;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetHasDimensionality;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Specifications
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetHasDimensionality
 * @internal
 */
class DatasetHasDimensionalityTest extends TestCase
{
    /**
     * @test
     * @dataProvider passesProvider
     *
     * @param DatasetHasDimensionality $specification
     * @param bool $expected
     * @param DatasetHasDimensionality $specification
     */
    public function passes(DatasetHasDimensionality $specification, bool $expected) : void
    {
        $this->assertSame($expected, $specification->passes());
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function passesProvider() : Generator
    {
        (yield [DatasetHasDimensionality::with(Unlabeled::quick([['swamp', 'island', 'black knight', 'counter spell']]), 4), \true]);
        (yield [DatasetHasDimensionality::with(Unlabeled::quick([[0.0, 1.0, 2.0]]), 4), \false]);
    }
}
