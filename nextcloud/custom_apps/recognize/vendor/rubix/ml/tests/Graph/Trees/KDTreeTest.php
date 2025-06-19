<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Graph\Trees;

use OCA\Recognize\Vendor\Rubix\ML\Graph\Trees\Tree;
use OCA\Recognize\Vendor\Rubix\ML\Graph\Trees\KDTree;
use OCA\Recognize\Vendor\Rubix\ML\Graph\Trees\Spatial;
use OCA\Recognize\Vendor\Rubix\ML\Graph\Trees\BinaryTree;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\Euclidean;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Agglomerate;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Trees
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Graph\Trees\KDTree
 * @internal
 */
class KDTreeTest extends TestCase
{
    protected const DATASET_SIZE = 100;
    protected const RANDOM_SEED = 0;
    /**
     * @var Agglomerate
     */
    protected $generator;
    /**
     * @var KDTree
     */
    protected $tree;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new Agglomerate(['east' => new Blob([5, -2, -2]), 'west' => new Blob([0, 5, -3])], [0.5, 0.5]);
        $this->tree = new KDTree(20, new Euclidean());
        \srand(self::RANDOM_SEED);
    }
    protected function assertPreConditions() : void
    {
        $this->assertEquals(0, $this->tree->height());
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(KDTree::class, $this->tree);
        $this->assertInstanceOf(Spatial::class, $this->tree);
        $this->assertInstanceOf(BinaryTree::class, $this->tree);
        $this->assertInstanceOf(Tree::class, $this->tree);
    }
    /**
     * @test
     */
    public function growNeighborsRange() : void
    {
        $this->tree->grow($this->generator->generate(self::DATASET_SIZE));
        $this->assertGreaterThan(2, $this->tree->height());
        $sample = $this->generator->generate(1)->sample(0);
        [$samples, $labels, $distances] = $this->tree->nearest($sample, 5);
        $this->assertCount(5, $samples);
        $this->assertCount(5, $labels);
        $this->assertCount(5, $distances);
        $this->assertCount(1, \array_unique($labels));
        [$samples, $labels, $distances] = $this->tree->range($sample, 5.0);
        $this->assertCount(50, $samples);
        $this->assertCount(50, $labels);
        $this->assertCount(50, $distances);
        $this->assertCount(1, \array_unique($labels));
    }
    /**
     * @test
     */
    public function growWithSameSamples() : void
    {
        $generator = new Agglomerate(['east' => new Blob([5, -2, 10], 0.0)]);
        $dataset = $generator->generate(self::DATASET_SIZE);
        $this->tree->grow($dataset);
        $this->assertEquals(2, $this->tree->height());
    }
}
