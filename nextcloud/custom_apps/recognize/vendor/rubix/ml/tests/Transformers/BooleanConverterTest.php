<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\BooleanConverter;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Transformer;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Transformers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Transformers\BooleanConverterTest
 * @internal
 */
class BooleanConverterTest extends TestCase
{
    /**
     * @var BooleanConverter
     */
    protected $transformer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->transformer = new BooleanConverter('!true!', '!false!');
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(BooleanConverter::class, $this->transformer);
        $this->assertInstanceOf(Transformer::class, $this->transformer);
    }
    /**
     * @test
     */
    public function transform() : void
    {
        $dataset = new Unlabeled([[\true, 'true', '1', 1], [\false, 'false', '0', 0]]);
        $dataset->apply($this->transformer);
        $expected = [['!true!', 'true', '1', 1], ['!false!', 'false', '0', 0]];
        $this->assertEquals($expected, $dataset->samples());
    }
}
