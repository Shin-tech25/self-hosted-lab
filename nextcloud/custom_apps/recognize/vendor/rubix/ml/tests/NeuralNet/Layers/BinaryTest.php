<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\NeuralNet\Layers;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\Deferred;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Layer;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Output;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Binary;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\Stochastic;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\CostFunctions\CrossEntropy;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Layers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Binary
 * @internal
 */
class BinaryTest extends TestCase
{
    protected const RANDOM_SEED = 0;
    /**
     * @var Matrix
     */
    protected $input;
    /**
     * @var string[]
     */
    protected $labels;
    /**
     * @var \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\Optimizer
     */
    protected $optimizer;
    /**
     * @var Binary
     */
    protected $layer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->input = Matrix::quick([[1.0, 2.5, -0.1]]);
        $this->labels = ['hot', 'cold', 'hot'];
        $this->optimizer = new Stochastic(0.001);
        $this->layer = new Binary(['hot', 'cold'], new CrossEntropy());
        \srand(self::RANDOM_SEED);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Binary::class, $this->layer);
        $this->assertInstanceOf(Output::class, $this->layer);
        $this->assertInstanceOf(Layer::class, $this->layer);
    }
    /**
     * @test
     */
    public function initializeForwardBackInfer() : void
    {
        $this->layer->initialize(1);
        $this->assertEquals(1, $this->layer->width());
        $expected = [[0.7310585786300049, 0.9241418199787566, 0.47502081252106]];
        $forward = $this->layer->forward($this->input);
        $this->assertInstanceOf(Matrix::class, $forward);
        $this->assertEqualsWithDelta($expected, $forward->asArray(), 1.0E-8);
        [$computation, $loss] = $this->layer->back($this->labels, $this->optimizer);
        $this->assertInstanceOf(Deferred::class, $computation);
        $this->assertIsFloat($loss);
        $gradient = $computation->compute();
        $expected = [[0.2436861928766683, -0.02528606000708115, 0.15834027084035332]];
        $this->assertInstanceOf(Matrix::class, $gradient);
        $this->assertEqualsWithDelta($expected, $gradient->asArray(), 1.0E-8);
        $expected = [[0.7310585786300049, 0.9241418199787566, 0.47502081252106]];
        $infer = $this->layer->infer($this->input);
        $this->assertInstanceOf(Matrix::class, $infer);
        $this->assertEqualsWithDelta($expected, $infer->asArray(), 1.0E-8);
    }
}
