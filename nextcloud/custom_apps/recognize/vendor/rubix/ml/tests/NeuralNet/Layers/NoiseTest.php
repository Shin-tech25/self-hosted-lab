<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\NeuralNet\Layers;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\Deferred;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Noise;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Layer;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Hidden;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\Stochastic;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Layers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Noise
 * @internal
 */
class NoiseTest extends TestCase
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
     * @var Noise
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
        $this->layer = new Noise(0.1);
        \srand(self::RANDOM_SEED);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Noise::class, $this->layer);
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
        $expected = [[0.9396596259960941, 2.408572590287506, -0.16793207202614419], [0.1457098686524435, -0.0783513312152093, 3.063132246060683], [-0.08825748362793215, -5.936776081560676, -0.5918333225801408]];
        $forward = $this->layer->forward($this->input);
        $this->assertInstanceOf(Matrix::class, $forward);
        $this->assertEqualsWithDelta($expected, $forward->asArray(), 1.0E-8);
        $gradient = $this->layer->back($this->prevGrad, $this->optimizer)->compute();
        $expected = [[0.25, 0.7, 0.1], [0.5, 0.2, 0.01], [0.25, 0.1, 0.89]];
        $this->assertInstanceOf(Matrix::class, $gradient);
        $this->assertEqualsWithDelta($expected, $gradient->asArray(), 1.0E-8);
        $expected = [[1.0, 2.5, -0.1], [0.1, 0.0, 3.0], [0.002, -6.0, -0.5]];
        $infer = $this->layer->infer($this->input);
        $this->assertInstanceOf(Matrix::class, $infer);
        $this->assertEqualsWithDelta($expected, $infer->asArray(), 1.0E-8);
    }
}
