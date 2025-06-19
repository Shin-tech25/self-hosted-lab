<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Transformers\Stateful;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Transformer;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\IntervalDiscretizer;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Transformers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Transformers\IntervalDiscretizer
 * @internal
 */
class IntervalDiscretizerTest extends TestCase
{
    /**
     * @var Blob
     */
    protected $generator;
    /**
     * @var IntervalDiscretizer
     */
    protected $transformer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new Blob([0.0, 4.0, 0.0, -1.5], [1.0, 5.0, 0.01, 10.0]);
        $this->transformer = new IntervalDiscretizer(5, \false);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(IntervalDiscretizer::class, $this->transformer);
        $this->assertInstanceOf(Transformer::class, $this->transformer);
        $this->assertInstanceOf(Stateful::class, $this->transformer);
    }
    /**
     * @test
     */
    public function fitTransform() : void
    {
        $dataset = $this->generator->generate(30);
        $this->transformer->fit($dataset);
        $this->assertTrue($this->transformer->fitted());
        $intervals = $this->transformer->intervals();
        $this->assertIsArray($intervals);
        $this->assertCount(4, $intervals);
        $this->assertContainsOnly('array', $intervals);
        $sample = $this->generator->generate(1)->apply($this->transformer)->sample(0);
        $this->assertCount(4, $sample);
        $expected = ['0', '1', '2', '3', '4'];
        $this->assertContains($sample[0], $expected);
        $this->assertContains($sample[1], $expected);
        $this->assertContains($sample[2], $expected);
        $this->assertContains($sample[3], $expected);
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
