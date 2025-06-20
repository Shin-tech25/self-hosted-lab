<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Elastic;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Stateful;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Reversible;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Transformer;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\MaxAbsoluteScaler;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Transformers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Transformers\MaxAbsoluteScaler
 * @internal
 */
class MaxAbsoluteScalerTest extends TestCase
{
    /**
     * @var Blob
     */
    protected $generator;
    /**
     * @var MaxAbsoluteScaler
     */
    protected $transformer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new Blob([0.0, 3000.0, -6.0], [1.0, 30.0, 0.001]);
        $this->transformer = new MaxAbsoluteScaler();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(MaxAbsoluteScaler::class, $this->transformer);
        $this->assertInstanceOf(Transformer::class, $this->transformer);
        $this->assertInstanceOf(Stateful::class, $this->transformer);
        $this->assertInstanceOf(Elastic::class, $this->transformer);
        $this->assertInstanceOf(Reversible::class, $this->transformer);
        $this->assertInstanceOf(Persistable::class, $this->transformer);
    }
    /**
     * @test
     */
    public function fitUpdateTransformReverse() : void
    {
        $this->transformer->fit($this->generator->generate(30));
        $this->transformer->update($this->generator->generate(30));
        $this->assertTrue($this->transformer->fitted());
        $maxabs = $this->transformer->maxabs();
        $this->assertIsArray($maxabs);
        $this->assertCount(3, $maxabs);
        $dataset = $this->generator->generate(1);
        $original = $dataset->sample(0);
        $dataset->apply($this->transformer);
        $sample = $dataset->sample(0);
        $this->assertCount(3, $sample);
        $this->assertEqualsWithDelta(0, $sample[0], 2 + 1.0E-8);
        $this->assertEqualsWithDelta(0, $sample[1], 2 + 1.0E-8);
        $this->assertEqualsWithDelta(0, $sample[2], 2 + 1.0E-8);
        $dataset->reverseApply($this->transformer);
        $this->assertEqualsWithDelta($original, $dataset->sample(0), 1.0E-8);
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
    /**
     * @test
     */
    public function reverseTransformUnfitted() : void
    {
        $this->expectException(RuntimeException::class);
        $samples = $this->generator->generate(1)->samples();
        $this->transformer->reverseTransform($samples);
    }
}
