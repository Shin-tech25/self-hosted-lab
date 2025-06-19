<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Stateful;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Transformer;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\HotDeckImputer;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Transformers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Transformers\HotDeckImputer
 * @internal
 */
class HotDeckImputerTest extends TestCase
{
    protected const RANDOM_SEED = 0;
    /**
     * @var Blob
     */
    protected $generator;
    /**
     * @var HotDeckImputer
     */
    protected $transformer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new Blob([30.0, 0.0]);
        $this->transformer = new HotDeckImputer(2, \true, '?');
        \srand(self::RANDOM_SEED);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(HotDeckImputer::class, $this->transformer);
        $this->assertInstanceOf(Transformer::class, $this->transformer);
        $this->assertInstanceOf(Stateful::class, $this->transformer);
    }
    /**
     * @test
     */
    public function fitTransform() : void
    {
        $dataset = new Unlabeled([[30, 0.001], [\NAN, 0.055], [50, -2.0], [60, \NAN], [10, 1.0], [100, 9.0]]);
        $this->transformer->fit($dataset);
        $this->assertTrue($this->transformer->fitted());
        $dataset->apply($this->transformer);
        $this->assertEquals(30, $dataset[1][0]);
        $this->assertEquals(-2.0, $dataset[3][1]);
    }
}
