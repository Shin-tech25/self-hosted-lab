<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\NeuralNet\Layers;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\Deferred;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Layer;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Output;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Continuous;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\Stochastic;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\CostFunctions\LeastSquares;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Layers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Continuous
 * @internal
 */
class ContinuousTest extends TestCase
{
    protected const RANDOM_SEED = 0;
    /**
     * @var Matrix
     */
    protected $input;
    /**
     * @var (int|float)[]
     */
    protected $labels;
    /**
     * @var \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\Optimizer
     */
    protected $optimizer;
    /**
     * @var Continuous
     */
    protected $layer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->input = Matrix::quick([[2.5, 0.0, -6.0]]);
        $this->labels = [0.0, -2.5, 90];
        $this->optimizer = new Stochastic(0.001);
        $this->layer = new Continuous(new LeastSquares());
        \srand(self::RANDOM_SEED);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Continuous::class, $this->layer);
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
        $expected = [[2.5, 0.0, -6.0]];
        $forward = $this->layer->forward($this->input);
        $this->assertInstanceOf(Matrix::class, $forward);
        $this->assertEqualsWithDelta($expected, $forward->asArray(), 1.0E-8);
        [$computation, $loss] = $this->layer->back($this->labels, $this->optimizer);
        $this->assertInstanceOf(Deferred::class, $computation);
        $this->assertIsFloat($loss);
        $gradient = $computation->compute();
        $expected = [[0.8333333333333334, 0.8333333333333334, -32.0]];
        $this->assertInstanceOf(Matrix::class, $gradient);
        $this->assertEqualsWithDelta($expected, $gradient->asArray(), 1.0E-8);
        $expected = [[2.5, 0.0, -6.0]];
        $infer = $this->layer->infer($this->input);
        $this->assertInstanceOf(Matrix::class, $infer);
        $this->assertEqualsWithDelta($expected, $infer->asArray(), 1.0E-8);
    }
}
