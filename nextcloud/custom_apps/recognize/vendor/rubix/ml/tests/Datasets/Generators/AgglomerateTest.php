<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Datasets\Generators;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Generator;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Agglomerate;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Generators
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Agglomerate
 * @internal
 */
class AgglomerateTest extends TestCase
{
    protected const DATASET_SIZE = 30;
    /**
     * @var Agglomerate
     */
    protected $generator;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new Agglomerate(['one' => new Blob([-5.0, 3.0], 0.2), 'two' => new Blob([5.0, -3.0], 0.2)], [1, 0.5]);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Agglomerate::class, $this->generator);
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
        $this->assertEquals(['one', 'two'], $dataset->possibleOutcomes());
    }
}
