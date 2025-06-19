<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Transformer;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\StopWordFilter;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Transformers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Transformers\StopWordFilter
 * @internal
 */
class StopWordFilterTest extends TestCase
{
    /**
     * @var StopWordFilter
     */
    protected $transformer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->transformer = new StopWordFilter(['a', 'quick', 'pig', 'à']);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(StopWordFilter::class, $this->transformer);
        $this->assertInstanceOf(Transformer::class, $this->transformer);
    }
    /**
     * @test
     */
    public function transform() : void
    {
        $dataset = Unlabeled::quick([['the quick brown fox jumped over the lazy man sitting at a bus' . ' stop drinking a can of coke'], ['with a dandy umbrella'], ['salle à manger']]);
        $dataset->apply($this->transformer);
        $expected = [['the  brown fox jumped over the lazy man sitting at  bus stop drinking  can of coke'], ['with  dandy umbrella'], ['salle  manger']];
        $this->assertEquals($expected, $dataset->samples());
    }
}
