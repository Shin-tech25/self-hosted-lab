<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Datasets\Generators;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\HalfMoon;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Generator;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Generators
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\HalfMoon
 * @internal
 */
class HalfMoonTest extends TestCase
{
    protected const DATASET_SIZE = 30;
    /**
     * @var HalfMoon
     */
    protected $generator;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new HalfMoon(5.0, 5.0, 10.0, 45.0, 0.1);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(HalfMoon::class, $this->generator);
        $this->assertInstanceOf(Generator::class, $this->generator);
    }
    /**
     * @test
     */
    public function dimensions() : void
    {
        $this->assertEquals(2, $this->generator->dimensions());
    }
    /**
     * @test
     */
    public function generate() : void
    {
        $dataset = $this->generator->generate(self::DATASET_SIZE);
        $this->assertInstanceOf(Labeled::class, $dataset);
        $this->assertInstanceOf(Dataset::class, $dataset);
        $this->assertCount(self::DATASET_SIZE, $dataset);
    }
}
