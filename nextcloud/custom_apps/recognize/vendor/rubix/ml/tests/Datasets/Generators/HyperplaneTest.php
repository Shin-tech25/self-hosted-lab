<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Datasets\Generators;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Hyperplane;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Generator;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Generators
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Hyperplane
 * @internal
 */
class HyperplaneTest extends TestCase
{
    /**
     * @var Hyperplane
     */
    protected $generator;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new Hyperplane([0.001, -4.0, 12], 5.0);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Hyperplane::class, $this->generator);
        $this->assertInstanceOf(Generator::class, $this->generator);
    }
    /**
     * @test
     */
    public function dimensions() : void
    {
        $this->assertEquals(3, $this->generator->dimensions());
    }
    /**
     * @test
     */
    public function generate() : void
    {
        $dataset = $this->generator->generate(30);
        $this->assertInstanceOf(Labeled::class, $dataset);
        $this->assertInstanceOf(Dataset::class, $dataset);
        $this->assertCount(30, $dataset);
    }
}
