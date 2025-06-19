<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Specifications;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\Hamming;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\Euclidean;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SamplesAreCompatibleWithDistance;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Specifications
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Specifications\SamplesAreCompatibleWithDistance
 * @internal
 */
class SamplesAreCompatibleWithDistanceTest extends TestCase
{
    /**
     * @test
     * @dataProvider passesProvider
     *
     * @param SamplesAreCompatibleWithDistance $specification
     * @param bool $expected
     */
    public function passes(SamplesAreCompatibleWithDistance $specification, bool $expected) : void
    {
        $this->assertSame($expected, $specification->passes());
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function passesProvider() : Generator
    {
        (yield [SamplesAreCompatibleWithDistance::with(Unlabeled::quick([['swamp', 'island', 'black knight', 'counter spell']]), new Hamming()), \true]);
        (yield [SamplesAreCompatibleWithDistance::with(Unlabeled::quick([[6.0, -1.1, 5, 'college']]), new Euclidean()), \false]);
        (yield [SamplesAreCompatibleWithDistance::with(Unlabeled::quick([[6.0, -1.1, 5, 'college']]), new Hamming()), \false]);
        (yield [SamplesAreCompatibleWithDistance::with(Unlabeled::quick([[1, 2, 3, 4, 5]]), new Euclidean()), \true]);
    }
}
