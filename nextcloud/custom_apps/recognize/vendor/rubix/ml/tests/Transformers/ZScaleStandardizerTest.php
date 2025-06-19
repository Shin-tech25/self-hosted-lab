<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Elastic;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Stateful;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Reversible;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Transformer;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\ZScaleStandardizer;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Transformers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Transformers\ZScaleStandardizer
 * @internal
 */
class ZScaleStandardizerTest extends TestCase
{
    /**
     * @var Blob
     */
    protected $generator;
    /**
     * @var ZScaleStandardizer
     */
    protected $transformer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new Blob([0.0, 3000.0, -6.0], [1.0, 30.0, 0.001]);
        $this->transformer = new ZScaleStandardizer(\true);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(ZScaleStandardizer::class, $this->transformer);
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
        $means = $this->transformer->means();
        $this->assertIsArray($means);
        $this->assertCount(3, $means);
        $this->assertContainsOnly('float', $means);
        $variances = $this->transformer->variances();
        $this->assertIsArray($variances);
        $this->assertCount(3, $variances);
        $this->assertContainsOnly('float', $variances);
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
