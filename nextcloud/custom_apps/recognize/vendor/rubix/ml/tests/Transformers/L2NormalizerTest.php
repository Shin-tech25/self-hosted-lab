<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Transformer;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\L2Normalizer;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Transformers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Transformers\L2Normalizer
 * @internal
 */
class L2NormalizerTest extends TestCase
{
    /**
     * @var L2Normalizer
     */
    protected $transformer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->transformer = new L2Normalizer();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(L2Normalizer::class, $this->transformer);
        $this->assertInstanceOf(Transformer::class, $this->transformer);
    }
    /**
     * @test
     */
    public function transform() : void
    {
        $dataset = new Unlabeled([[1, 2, 3, 4], [40, 0, 30, 10], [100, 300, 200, 400]]);
        $dataset->apply($this->transformer);
        $expected = [[0.18257418583505536, 0.3651483716701107, 0.5477225575051661, 0.7302967433402214], [0.7844645405527362, 0.0, 0.5883484054145521, 0.19611613513818404], [0.18257418583505536, 0.5477225575051661, 0.3651483716701107, 0.7302967433402214]];
        $this->assertEqualsWithDelta($expected, $dataset->samples(), 1.0E-8);
    }
}
