<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\NeuralNet;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Parameter;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\Stochastic;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group NeuralNet
 * @covers \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Parameter
 * @internal
 */
class ParameterTest extends TestCase
{
    /**
     * @var Parameter
     */
    protected Parameter $param;
    /**
     * @var \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\Optimizer
     */
    protected \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\Optimizer $optimizer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->param = new Parameter(Matrix::quick([[5, 4], [-2, 6]]));
        $this->optimizer = new Stochastic();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Parameter::class, $this->param);
    }
    /**
     * @test
     */
    public function id() : void
    {
        $this->assertIsInt($this->param->id());
    }
    /**
     * @test
     */
    public function update() : void
    {
        $gradient = Matrix::quick([[2, 1], [1, -2]]);
        $expected = [[4.98, 3.99], [-2.01, 6.02]];
        $this->param->update($gradient, $this->optimizer);
        $this->assertEquals($expected, $this->param->param()->asArray());
    }
}
