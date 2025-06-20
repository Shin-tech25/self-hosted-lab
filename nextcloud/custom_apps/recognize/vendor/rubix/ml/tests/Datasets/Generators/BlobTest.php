<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Datasets\Generators;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Generator;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Generators
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob
 * @internal
 */
class BlobTest extends TestCase
{
    protected const DATASET_SIZE = 30;
    /**
     * @var Blob
     */
    protected $generator;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new Blob([0, 0, 0], 1.0);
    }
    /**
     * @test
     */
    public function simulate() : void
    {
        $dataset = $this->generator->generate(100);
        $generator = Blob::simulate($dataset);
        $this->assertInstanceOf(Blob::class, $generator);
        $this->assertInstanceOf(Generator::class, $generator);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Blob::class, $this->generator);
        $this->assertInstanceOf(Generator::class, $this->generator);
    }
    /**
     * @test
     */
    public function center() : void
    {
        $this->assertEquals([0, 0, 0], $this->generator->center());
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
        $this->assertInstanceOf(Unlabeled::class, $dataset);
        $this->assertInstanceOf(Dataset::class, $dataset);
        $this->assertCount(self::DATASET_SIZE, $dataset);
    }
}
