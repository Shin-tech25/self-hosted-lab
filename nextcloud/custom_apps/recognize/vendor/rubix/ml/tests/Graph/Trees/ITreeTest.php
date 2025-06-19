<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Graph\Trees;

use OCA\Recognize\Vendor\Rubix\ML\Graph\Trees\Tree;
use OCA\Recognize\Vendor\Rubix\ML\Graph\Nodes\Depth;
use OCA\Recognize\Vendor\Rubix\ML\Graph\Trees\ITree;
use OCA\Recognize\Vendor\Rubix\ML\Graph\Trees\BinaryTree;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Agglomerate;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Trees
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Graph\Trees\ITree
 * @internal
 */
class ITreeTest extends TestCase
{
    protected const DATASET_SIZE = 100;
    protected const RANDOM_SEED = 0;
    /**
     * @var Agglomerate
     */
    protected $generator;
    /**
     * @var ITree
     */
    protected $tree;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new Agglomerate(['east' => new Blob([5, -2, -2]), 'west' => new Blob([0, 5, -3])], [0.5, 0.5]);
        $this->tree = new ITree();
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
        $this->assertInstanceOf(ITree::class, $this->tree);
        $this->assertInstanceOf(BinaryTree::class, $this->tree);
        $this->assertInstanceOf(Tree::class, $this->tree);
    }
    /**
     * @test
     */
    public function growSearch() : void
    {
        $this->tree->grow($this->generator->generate(self::DATASET_SIZE));
        $this->assertGreaterThan(5, $this->tree->height());
        $sample = $this->generator->generate(1)->sample(0);
        $node = $this->tree->search($sample);
        $this->assertInstanceOf(Depth::class, $node);
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
