<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Verbose;
use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Loggers\BlackHole;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\TSNE;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\Euclidean;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Agglomerate;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Transformers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Transformers\TSNE
 * @internal
 */
class TSNETest extends TestCase
{
    /**
     * The number of samples in the validation set.
     *
     * @var int
     */
    protected const TEST_SIZE = 30;
    /**
     * Constant used to see the random number generator.
     *
     * @var int
     */
    protected const RANDOM_SEED = 0;
    /**
     * @var Agglomerate
     */
    protected $generator;
    /**
     * @var TSNE
     */
    protected $embedder;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new Agglomerate(['red' => new Blob([255, 32, 0], 30.0), 'green' => new Blob([0, 128, 0], 10.0), 'blue' => new Blob([0, 32, 255], 20.0)], [2, 3, 4]);
        $this->embedder = new TSNE(1, 10.0, 10, 12.0, 500, 1.0E-7, 10, new Euclidean());
        $this->embedder->setLogger(new BlackHole());
        \srand(self::RANDOM_SEED);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(TSNE::class, $this->embedder);
        $this->assertInstanceOf(Verbose::class, $this->embedder);
    }
    /**
     * @test
     */
    public function badNumDimensions() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new TSNE(0);
    }
    /**
     * @test
     */
    public function compatibility() : void
    {
        $expected = [DataType::continuous()];
        $this->assertEquals($expected, $this->embedder->compatibility());
    }
    /**
     * @test
     */
    public function transform() : void
    {
        $dataset = $this->generator->generate(self::TEST_SIZE);
        $dataset->apply($this->embedder);
        $this->assertCount(self::TEST_SIZE, $dataset);
        $this->assertCount(1, $dataset->sample(0));
        $losses = $this->embedder->losses();
        $this->assertIsArray($losses);
        $this->assertContainsOnly('float', $losses);
    }
}
