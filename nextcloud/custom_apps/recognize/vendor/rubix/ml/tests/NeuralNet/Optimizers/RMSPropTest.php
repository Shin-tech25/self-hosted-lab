<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\NeuralNet\Optimizers;

use OCA\Recognize\Vendor\Tensor\Tensor;
use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Parameter;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\RMSProp;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\Adaptive;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\Optimizer;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Optimizers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\RMSProp
 * @internal
 */
class RMSPropTest extends TestCase
{
    /**
     * @var RMSProp
     */
    protected $optimizer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->optimizer = new RMSProp(0.001, 0.1);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(RMSProp::class, $this->optimizer);
        $this->assertInstanceOf(Adaptive::class, $this->optimizer);
        $this->assertInstanceOf(Optimizer::class, $this->optimizer);
    }
    /**
     * @test
     * @dataProvider stepProvider
     *
     * @param Parameter $param
     * @param \Tensor\Tensor<int|float> $gradient
     * @param list<list<float>> $expected
     */
    public function step(Parameter $param, Tensor $gradient, array $expected) : void
    {
        $this->optimizer->warm($param);
        $step = $this->optimizer->step($param, $gradient);
        $this->assertEquals($expected, $step->asArray());
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function stepProvider() : Generator
    {
        (yield [new Parameter(Matrix::quick([[0.1, 0.6, -0.4], [0.5, 0.6, -0.4], [0.1, 0.1, -0.7]])), Matrix::quick([[0.01, 0.05, -0.02], [-0.01, 0.02, 0.03], [0.04, -0.01, -0.5]]), [[0.0031622776601683794, 0.003162277660168379, -0.0031622776601683794], [-0.0031622776601683794, 0.0031622776601683794, 0.0031622776601683794], [0.0031622776601683794, -0.0031622776601683794, -0.0031622776601683794]]]);
    }
}
