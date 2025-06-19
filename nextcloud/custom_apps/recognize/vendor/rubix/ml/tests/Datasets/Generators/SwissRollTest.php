<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Datasets\Generators;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\SwissRoll;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Generator;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Generators
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\SwissRoll
 * @internal
 */
class SwissRollTest extends TestCase
{
    protected const DATASET_SIZE = 30;
    /**
     * @var SwissRoll
     */
    protected $generator;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new SwissRoll(0.0, 0.0, 0.0, 1.0, 12.0, 0.3);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(SwissRoll::class, $this->generator);
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
        $dataset = $this->generator->generate(self::DATASET_SIZE);
        $this->assertInstanceOf(Labeled::class, $dataset);
        $this->assertInstanceOf(Dataset::class, $dataset);
        $this->assertCount(self::DATASET_SIZE, $dataset);
    }
}
