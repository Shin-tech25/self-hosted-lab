<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Specifications;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsNotEmpty;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Specifications
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsNotEmpty
 * @internal
 */
class DatasetIsNotEmptyTest extends TestCase
{
    /**
     * @test
     * @dataProvider passesProvider
     *
     * @param DatasetIsNotEmpty $specification
     * @param bool $expected
     */
    public function passes(DatasetIsNotEmpty $specification, bool $expected) : void
    {
        $this->assertSame($expected, $specification->passes());
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function passesProvider() : Generator
    {
        (yield [DatasetIsNotEmpty::with(Unlabeled::quick([['swamp', 'island', 'black knight', 'counter spell']])), \true]);
        (yield [DatasetIsNotEmpty::with(Unlabeled::quick()), \false]);
    }
}
