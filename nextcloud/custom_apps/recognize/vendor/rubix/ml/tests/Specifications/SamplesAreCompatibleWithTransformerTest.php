<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Specifications;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\L1Normalizer;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\OneHotEncoder;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\TfIdfTransformer;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SamplesAreCompatibleWithTransformer;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Specifications
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Specifications\SamplesAreCompatibleWithTransformer
 * @internal
 */
class SamplesAreCompatibleWithTransformerTest extends TestCase
{
    /**
     * @test
     * @dataProvider passesProvider
     *
     * @param SamplesAreCompatibleWithTransformer $specification
     * @param bool $expected
     */
    public function passes(SamplesAreCompatibleWithTransformer $specification, bool $expected) : void
    {
        $this->assertSame($expected, $specification->passes());
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function passesProvider() : Generator
    {
        (yield [SamplesAreCompatibleWithTransformer::with(Unlabeled::quick([[6.0, -1.1, 5, 'college']]), new L1Normalizer()), \false]);
        (yield [SamplesAreCompatibleWithTransformer::with(Unlabeled::quick([[1, 2, 3, 4, 5]]), new L1Normalizer()), \true]);
        (yield [SamplesAreCompatibleWithTransformer::with(Unlabeled::quick([[6.0, -1.1, 5, 'college']]), new OneHotEncoder()), \true]);
        (yield [SamplesAreCompatibleWithTransformer::with(Unlabeled::quick([[6.0, -1.1, 5, 'college']]), new TfIdfTransformer()), \false]);
    }
}
