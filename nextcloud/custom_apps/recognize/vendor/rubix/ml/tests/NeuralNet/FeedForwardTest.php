<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\NeuralNet;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Network;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\FeedForward;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Dense;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Output;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\Adam;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Activation;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Multiclass;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Placeholder1D;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\ActivationFunctions\ReLU;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\CostFunctions\CrossEntropy;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group NeuralNet
 * @covers \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\FeedForward
 * @internal
 */
class FeedForwardTest extends TestCase
{
    /**
     * @var Labeled
     */
    protected $dataset;
    /**
     * @var FeedForward
     */
    protected $network;
    /**
     * @var \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Input
     */
    protected $input;
    /**
     * @var \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Hidden[]
     */
    protected $hidden;
    /**
     * @var Output
     */
    protected $output;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->dataset = Labeled::quick([[1.0, 2.5], [0.1, 0.0], [0.002, -6.0]], ['yes', 'no', 'maybe']);
        $this->input = new Placeholder1D(2);
        $this->hidden = [new Dense(10), new Activation(new ReLU()), new Dense(5), new Activation(new ReLU()), new Dense(3)];
        $this->output = new Multiclass(['yes', 'no', 'maybe'], new CrossEntropy());
        $this->network = new FeedForward($this->input, $this->hidden, $this->output, new Adam(0.001));
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(FeedForward::class, $this->network);
        $this->assertInstanceOf(Network::class, $this->network);
    }
    /**
     * @test
     */
    public function layers() : void
    {
        $this->assertCount(5, \iterator_to_array($this->network->layers()));
    }
    /**
     * @test
     */
    public function input() : void
    {
        $this->assertInstanceOf(Placeholder1D::class, $this->network->input());
    }
    /**
     * @test
     */
    public function hidden() : void
    {
        $this->assertCount(5, $this->network->hidden());
    }
    /**
     * @test
     */
    public function output() : void
    {
        $this->assertInstanceOf(Output::class, $this->network->output());
    }
    /**
     * @test
     */
    public function numParams() : void
    {
        $this->network->initialize();
        $this->assertEquals(103, $this->network->numParams());
    }
    /**
     * @test
     */
    public function roundtrip() : void
    {
        $this->network->initialize();
        $loss = $this->network->roundtrip($this->dataset);
        $this->assertIsFloat($loss);
    }
}
