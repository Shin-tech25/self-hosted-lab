<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Transformer;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\PolynomialExpander;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Transformers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Transformers\PolynomialExpander
 * @internal
 */
class PolynomialExpanderTest extends TestCase
{
    /**
     * @var PolynomialExpander
     */
    protected $transformer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->transformer = new PolynomialExpander(2);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(PolynomialExpander::class, $this->transformer);
        $this->assertInstanceOf(Transformer::class, $this->transformer);
    }
    /**
     * @test
     */
    public function transform() : void
    {
        $dataset = new Unlabeled([[1, 2, 3, 4], [40, 20, 30, 10], [100, 300, 200, 400]]);
        $dataset->apply($this->transformer);
        $expected = [[1, 1, 2, 4, 3, 9, 4, 16], [40, 1600, 20, 400, 30, 900, 10, 100], [100, 10000, 300, 90000, 200, 40000, 400, 160000]];
        $this->assertEqualsWithDelta($expected, $dataset->samples(), 1.0E-8);
    }
}
