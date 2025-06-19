<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Stateful;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Transformer;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\OneHotEncoder;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Transformers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Transformers\OneHotEncoder
 * @internal
 */
class OneHotEncoderTest extends TestCase
{
    /**
     * @var OneHotEncoder
     */
    protected $transformer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->transformer = new OneHotEncoder();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(OneHotEncoder::class, $this->transformer);
        $this->assertInstanceOf(Transformer::class, $this->transformer);
        $this->assertInstanceOf(Stateful::class, $this->transformer);
    }
    /**
     * @test
     */
    public function fitTransform() : void
    {
        $dataset = new Unlabeled([['nice', 'furry', 'friendly'], ['mean', 'furry', 'loner'], ['nice', 'rough', 'friendly'], ['mean', 'rough', 'friendly']]);
        $this->transformer->fit($dataset);
        $this->assertTrue($this->transformer->fitted());
        $categories = $this->transformer->categories();
        $this->assertIsArray($categories);
        $this->assertCount(3, $categories);
        $this->assertContainsOnly('array', $categories);
        $dataset->apply($this->transformer);
        $expected = [[1, 0, 1, 0, 1, 0], [0, 1, 1, 0, 0, 1], [1, 0, 0, 1, 1, 0], [0, 1, 0, 1, 1, 0]];
        $this->assertEquals($expected, $dataset->samples());
    }
}
