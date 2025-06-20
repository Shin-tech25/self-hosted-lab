<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Transformers\Stateful;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Transformer;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\SparseRandomProjector;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Transformers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Transformers\SparseRandomProjector
 * @internal
 */
class SparseRandomProjectorTest extends TestCase
{
    /**
     * Constant used to see the random number generator.
     *
     * @var int
     */
    protected const RANDOM_SEED = 0;
    /**
     * @var Blob
     */
    protected $generator;
    /**
     * @var SparseRandomProjector
     */
    protected $transformer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new Blob(\array_fill(0, 10, 0.0), 3.0);
        $this->transformer = new SparseRandomProjector(4);
        \srand(self::RANDOM_SEED);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(SparseRandomProjector::class, $this->transformer);
        $this->assertInstanceOf(Transformer::class, $this->transformer);
        $this->assertInstanceOf(Stateful::class, $this->transformer);
    }
    /**
     * @test
     */
    public function fitTransform() : void
    {
        $this->assertCount(10, $this->generator->generate(1)->sample(0));
        $this->transformer->fit($this->generator->generate(30));
        $this->assertTrue($this->transformer->fitted());
        $expected = [3.8861419746435, -17.801078083484, 0.29819783331323, -12.191560356574];
        $sample = $this->generator->generate(1)->apply($this->transformer)->sample(0);
        $this->assertCount(4, $sample);
        $this->assertEqualsWithDelta($expected, $sample, 1.0E-8);
    }
    /**
     * @test
     */
    public function transformUnfitted() : void
    {
        $this->expectException(RuntimeException::class);
        $samples = $this->generator->generate(1)->samples();
        $this->transformer->transform($samples);
    }
}
