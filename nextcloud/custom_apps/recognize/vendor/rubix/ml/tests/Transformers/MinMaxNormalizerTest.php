<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Elastic;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Stateful;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Reversible;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Transformer;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\MinMaxNormalizer;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Transformers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Transformers\MinMaxNormalizer
 * @internal
 */
class MinMaxNormalizerTest extends TestCase
{
    /**
     * @var Blob
     */
    protected $generator;
    /**
     * @var MinMaxNormalizer
     */
    protected $transformer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new Blob([0.0, 3000.0, -6.0, 1.0], [1.0, 30.0, 0.001, 0.0]);
        $this->transformer = new MinMaxNormalizer(0.0, 1.0);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(MinMaxNormalizer::class, $this->transformer);
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
        $minimums = $this->transformer->minimums();
        $this->assertIsArray($minimums);
        $this->assertCount(4, $minimums);
        $maximums = $this->transformer->maximums();
        $this->assertIsArray($maximums);
        $this->assertCount(4, $maximums);
        $dataset = $this->generator->generate(1);
        $original = $dataset->sample(0);
        $dataset->apply($this->transformer);
        $sample = $dataset->sample(0);
        $this->assertCount(4, $sample);
        $this->assertEqualsWithDelta(0.5, $sample[0], 1);
        $this->assertEqualsWithDelta(0.5, $sample[1], 1);
        $this->assertEqualsWithDelta(0.5, $sample[2], 1);
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
}
