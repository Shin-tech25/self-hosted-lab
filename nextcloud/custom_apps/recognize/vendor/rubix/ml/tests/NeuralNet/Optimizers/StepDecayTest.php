<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\NeuralNet\Optimizers;

use OCA\Recognize\Vendor\Tensor\Tensor;
use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Parameter;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\StepDecay;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\Optimizer;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Optimizers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\StepDecay
 * @internal
 */
class StepDecayTest extends TestCase
{
    /**
     * @var StepDecay
     */
    protected $optimizer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->optimizer = new StepDecay(0.001);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(StepDecay::class, $this->optimizer);
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
        $step = $this->optimizer->step($param, $gradient);
        $this->assertEquals($expected, $step->asArray());
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function stepProvider() : Generator
    {
        (yield [new Parameter(Matrix::quick([[0.1, 0.6, -0.4], [0.5, 0.6, -0.4], [0.1, 0.1, -0.7]])), Matrix::quick([[0.01, 0.05, -0.02], [-0.01, 0.02, 0.03], [0.04, -0.01, -0.5]]), [[1.0E-5, 5.0E-5, -2.0E-5], [-1.0E-5, 2.0E-5, 3.0E-5], [4.0E-5, -1.0E-5, -0.0005]]]);
    }
}
