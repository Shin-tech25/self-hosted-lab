<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Transformer;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\ImageResizer;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Transformers
 * @requires extension gd
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Transformers\ImageResizer
 * @internal
 */
class ImageResizerTest extends TestCase
{
    /**
     * @var ImageResizer
     */
    protected $transformer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->transformer = new ImageResizer(32, 32);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(ImageResizer::class, $this->transformer);
        $this->assertInstanceOf(Transformer::class, $this->transformer);
    }
    /**
     * @test
     */
    public function transform() : void
    {
        $dataset = Unlabeled::quick([[\imagecreatefrompng('./tests/test.png'), 'whatever', 69]]);
        $dataset->apply($this->transformer);
        $sample = $dataset->sample(0);
        $image = $sample[0];
        $this->assertEquals(32, \imagesx($image));
        $this->assertEquals(32, \imagesy($image));
    }
}
