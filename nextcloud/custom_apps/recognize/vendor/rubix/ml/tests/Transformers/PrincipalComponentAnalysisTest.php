<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Transformers\Stateful;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\Transformer;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\PrincipalComponentAnalysis;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Transformers
 * @requires extension tensor
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Transformers\PrincipalComponentAnalysis
 * @internal
 */
class PrincipalComponentAnalysisTest extends TestCase
{
    /**
     * @var Blob
     */
    protected $generator;
    /**
     * @var PrincipalComponentAnalysis
     */
    protected $transformer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new Blob([0.0, 3000.0, -6.0, 25], [1.0, 30.0, 0.001, 10.0]);
        $this->transformer = new PrincipalComponentAnalysis(2);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(PrincipalComponentAnalysis::class, $this->transformer);
        $this->assertInstanceOf(Transformer::class, $this->transformer);
        $this->assertInstanceOf(Stateful::class, $this->transformer);
    }
    /**
     * @test
     */
    public function fitTransform() : void
    {
        $this->assertEquals(4, $this->generator->dimensions());
        $this->transformer->fit($this->generator->generate(30));
        $this->assertTrue($this->transformer->fitted());
        $sample = $this->generator->generate(1)->apply($this->transformer)->sample(0);
        $this->assertCount(2, $sample);
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
