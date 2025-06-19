<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Graph\Nodes;

use OCA\Recognize\Vendor\Rubix\ML\Graph\Nodes\Box;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
use OCA\Recognize\Vendor\Rubix\ML\Graph\Nodes\Node;
use OCA\Recognize\Vendor\Rubix\ML\Graph\Nodes\Hypercube;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Nodes
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Graph\Nodes\Box
 * @internal
 */
class BoxTest extends TestCase
{
    protected const COLUMN = 1;
    protected const VALUE = 3.0;
    protected const SAMPLES = [[5.0, 2.0, -3], [6.0, 4.0, -5]];
    protected const LABELS = [22, 13];
    protected const MIN = [5.0, 2.0, -5];
    protected const MAX = [6.0, 4.0, -3];
    protected const BOX = [self::MIN, self::MAX];
    /**
     * @var Box
     */
    protected $node;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $subsets = [Labeled::quick([self::SAMPLES[0]], [self::LABELS[0]]), Labeled::quick([self::SAMPLES[1]], [self::LABELS[1]])];
        $this->node = new Box(self::COLUMN, self::VALUE, $subsets, self::MIN, self::MAX);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Box::class, $this->node);
        $this->assertInstanceOf(Hypercube::class, $this->node);
        $this->assertInstanceOf(Node::class, $this->node);
    }
    /**
     * @test
     */
    public function split() : void
    {
        $node = Box::split(Labeled::quick(self::SAMPLES, self::LABELS));
        $this->assertEquals(self::BOX, \iterator_to_array($node->sides()));
    }
    /**
     * @test
     */
    public function column() : void
    {
        $this->assertSame(self::COLUMN, $this->node->column());
    }
    /**
     * @test
     */
    public function value() : void
    {
        $this->assertSame(self::VALUE, $this->node->value());
    }
    /**
     * @test
     */
    public function subsets() : void
    {
        $expected = [Labeled::quick([self::SAMPLES[0]], [self::LABELS[0]]), Labeled::quick([self::SAMPLES[1]], [self::LABELS[1]])];
        $this->assertEquals($expected, $this->node->subsets());
    }
    /**
     * @test
     */
    public function sides() : void
    {
        $this->assertEquals(self::BOX, \iterator_to_array($this->node->sides()));
    }
    /**
     * @test
     */
    public function cleanup() : void
    {
        $subsets = $this->node->subsets();
        $this->assertIsArray($subsets);
        $this->assertCount(2, $subsets);
        $this->node->cleanup();
        $this->expectException(RuntimeException::class);
        $this->node->subsets();
    }
}
