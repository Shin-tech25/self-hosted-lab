<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\NeuralNet\Layers;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\Deferred;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\PReLU;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Layer;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Hidden;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Parametric;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\Stochastic;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Initializers\Constant;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Layers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\PReLU
 * @internal
 */
class PReLUTest extends TestCase
{
    protected const RANDOM_SEED = 0;
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
     * @var PReLU
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
        $this->layer = new PReLU(new Constant(0.25));
        \srand(self::RANDOM_SEED);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(PReLU::class, $this->layer);
        $this->assertInstanceOf(Layer::class, $this->layer);
        $this->assertInstanceOf(Hidden::class, $this->layer);
        $this->assertInstanceOf(Parametric::class, $this->layer);
    }
    /**
     * @test
     */
    public function initializeForwardBackInfer() : void
    {
        $this->layer->initialize($this->fanIn);
        $this->assertEquals($this->fanIn, $this->layer->width());
        $forward = $this->layer->forward($this->input);
        $expected = [[1.0, 2.5, -0.025], [0.1, 0.0, 3.0], [0.002, -1.5, -0.125]];
        $this->assertInstanceOf(Matrix::class, $forward);
        $this->assertEquals($expected, $forward->asArray());
        $gradient = $this->layer->back($this->prevGrad, $this->optimizer)->compute();
        $expected = [[0.25, 0.7, 0.025001000000000002], [0.5, 0.05, 0.01], [0.25, 0.025104500000000002, 0.22343005000000002]];
        $this->assertInstanceOf(Matrix::class, $gradient);
        $this->assertEquals($expected, $gradient->asArray());
        $expected = [[1.0, 2.5, -0.025001000000000002], [0.1, 0.0, 3.0], [0.002, -1.5062700000000002, -0.1255225]];
        $infer = $this->layer->infer($this->input);
        $this->assertInstanceOf(Matrix::class, $infer);
        $this->assertEquals($expected, $infer->asArray());
    }
}
