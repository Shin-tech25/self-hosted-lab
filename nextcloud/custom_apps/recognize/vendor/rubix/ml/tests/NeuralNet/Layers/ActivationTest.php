<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\NeuralNet\Layers;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\Deferred;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Layer;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Hidden;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Activation;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\Stochastic;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\ActivationFunctions\ReLU;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Layers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Activation
 * @internal
 */
class ActivationTest extends TestCase
{
    /**
     * @var positive-int
     */
    protected $fanIn;
    /**
     * @var Matrix
     */
    protected $input;
    /**
     * @var Deferred
     */
    protected $prevGrad;
    /**
     * @var \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\Optimizer
     */
    protected $optimizer;
    /**
     * @var Activation
     */
    protected $layer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->fanIn = 3;
        $this->input = Matrix::quick([[1.0, 2.5, -0.1], [0.1, 0.0, 3.0], [0.002, -6.0, -0.5]]);
        $this->prevGrad = new Deferred(function () {
            return Matrix::quick([[0.25, 0.7, 0.1], [0.5, 0.2, 0.01], [0.25, 0.1, 0.89]]);
        });
        $this->optimizer = new Stochastic(0.001);
        $this->layer = new Activation(new ReLU());
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Activation::class, $this->layer);
        $this->assertInstanceOf(Layer::class, $this->layer);
        $this->assertInstanceOf(Hidden::class, $this->layer);
    }
    /**
     * @test
     */
    public function initializeForwardBackInfer() : void
    {
        $this->layer->initialize($this->fanIn);
        $this->assertEquals($this->fanIn, $this->layer->width());
        $expected = [[1.0, 2.5, 0.0], [0.1, 0.0, 3.0], [0.002, 0.0, 0.0]];
        $forward = $this->layer->forward($this->input);
        $this->assertInstanceOf(Matrix::class, $forward);
        $this->assertEquals($expected, $forward->asArray());
        $gradient = $this->layer->back($this->prevGrad, $this->optimizer)->compute();
        $expected = [[0.25, 0.7, 0.0], [0.5, 0.0, 0.01], [0.25, 0, 0.0]];
        $this->assertInstanceOf(Matrix::class, $gradient);
        $this->assertEquals($expected, $gradient->asArray());
        $expected = [[1.0, 2.5, 0.0], [0.1, 0.0, 3.0], [0.002, 0.0, 0.0]];
        $infer = $this->layer->infer($this->input);
        $this->assertInstanceOf(Matrix::class, $infer);
        $this->assertEquals($expected, $infer->asArray());
    }
}
