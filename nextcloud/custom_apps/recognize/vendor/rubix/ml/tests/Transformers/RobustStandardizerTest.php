<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Stateful;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Reversible;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Transformer;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\RobustStandardizer;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Transformers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Transformers\RobustStandardizer
 * @internal
 */
class RobustStandardizerTest extends TestCase
{
    /**
     * @var Blob
     */
    protected $generator;
    /**
     * @var RobustStandardizer
     */
    protected $transformer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new Blob([0.0, 3000.0, -6.0], [1.0, 30.0, 0.001]);
        $this->transformer = new RobustStandardizer(\true);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(RobustStandardizer::class, $this->transformer);
        $this->assertInstanceOf(Transformer::class, $this->transformer);
        $this->assertInstanceOf(Stateful::class, $this->transformer);
        $this->assertInstanceOf(Reversible::class, $this->transformer);
        $this->assertInstanceOf(Persistable::class, $this->transformer);
    }
    /**
     * @test
     */
    public function fitUpdateTransformReverse() : void
    {
        $this->transformer->fit($this->generator->generate(30));
        $this->assertTrue($this->transformer->fitted());
        $medians = $this->transformer->medians();
        $this->assertIsArray($medians);
        $this->assertCount(3, $medians);
        $this->assertContainsOnly('float', $medians);
        $mads = $this->transformer->mads();
        $this->assertIsArray($mads);
        $this->assertCount(3, $mads);
        $this->assertContainsOnly('float', $mads);
        $dataset = $this->generator->generate(1);
        $original = $dataset->sample(0);
        $dataset->apply($this->transformer);
        $sample = $dataset->sample(0);
        $this->assertCount(3, $sample);
        $this->assertEqualsWithDelta(0, $sample[0], 6);
        $this->assertEqualsWithDelta(0, $sample[1], 6);
        $this->assertEqualsWithDelta(0, $sample[2], 6);
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
