<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Transformer;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\TextNormalizer;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Transformers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Transformers\TextNormalizer
 * @internal
 */
class TextNormalizerTest extends TestCase
{
    /**
     * @var TextNormalizer
     */
    protected $transformer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->transformer = new TextNormalizer(\true);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(TextNormalizer::class, $this->transformer);
        $this->assertInstanceOf(Transformer::class, $this->transformer);
    }
    /**
     * @test
     */
    public function transform() : void
    {
        $dataset = Unlabeled::quick([['The quick brown fox jumped over the lazy man sitting at a bus' . ' stop drinking a can of Coke'], ['with a Dandy   umbrella']]);
        $dataset->apply($this->transformer);
        $expected = [['THE QUICK BROWN FOX JUMPED OVER THE LAZY MAN SITTING AT A BUS STOP DRINKING A CAN OF COKE'], ['WITH A DANDY   UMBRELLA']];
        $this->assertEquals($expected, $dataset->samples());
    }
}
