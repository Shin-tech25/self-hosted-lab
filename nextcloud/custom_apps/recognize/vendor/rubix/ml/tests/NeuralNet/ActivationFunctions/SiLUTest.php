<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\NeuralNet\ActivationFunctions;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\ActivationFunctions\SiLU;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\ActivationFunctions\ActivationFunction;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group ActivationFunctions
 * @covers \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\ActivationFunctions\SiLU
 * @internal
 */
class SiLUTest extends TestCase
{
    /**
     * @var SiLU
     */
    protected $activationFn;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->activationFn = new SiLU();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(SiLU::class, $this->activationFn);
        $this->assertInstanceOf(ActivationFunction::class, $this->activationFn);
    }
    /**
     * @test
     * @dataProvider computeProvider
     *
     * @param Matrix $input
     * @param list<list<float>> $expected $expected
     */
    public function compute(Matrix $input, array $expected) : void
    {
        $activations = $this->activationFn->activate($input)->asArray();
        $this->assertEqualsWithDelta($expected, $activations, 1.0E-8);
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function computeProvider() : Generator
    {
        (yield [Matrix::quick([[1.0, -0.5, 0.0, 20.0, -10.0]]), [[0.7310585786300049, -0.1887703343990727, 0.0, 19.999999958776925, -0.00045397868702434395]]]);
        (yield [Matrix::quick([[-0.12, 0.31, -0.49], [0.99, 0.08, -0.03], [0.05, -0.52, 0.54]]), [[-0.056404313788251385, 0.17883443095093435, -0.18614784815188584], [0.7217970431258135, 0.04159914721244655, -0.014775016873481388], [0.025624869824210517, -0.1938831615171383, 0.3411787055774949]]]);
    }
    /**
     * @test
     * @dataProvider differentiateProvider
     *
     * @param Matrix $input
     * @param Matrix $activations
     * @param list<list<float>> $expected $expected
     */
    public function differentiate(Matrix $input, Matrix $activations, array $expected) : void
    {
        $derivatives = $this->activationFn->differentiate($input, $activations)->asArray();
        $this->assertEqualsWithDelta($expected, $derivatives, 1.0E-8);
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function differentiateProvider() : Generator
    {
        (yield [Matrix::quick([[1.0, -0.5, 0.001, 20.0, -10.0]]), Matrix::quick([[0.7310585786300049, -0.1887703343990727, 0.0, 19.999999958776925, -0.00045397868702434395]]), [[0.9276705118714867, 0.2600388126973482, 0.0, 1.0000000391619217, -0.0004085602086570823]]]);
        (yield [Matrix::quick([[-0.12, 0.31, -0.49], [0.99, 0.08, -0.03], [0.05, -0.52, 0.54]]), Matrix::quick([[-0.056404313788251385, 0.17883443095093435, -0.18614784815188584], [0.7217970431258135, 0.04159914721244655, -0.014775016873481388], [0.025624869824210517, -0.1938831615171383, 0.3411787055774949]]), [[0.44014368956320615, 0.65255274468445, 0.26446208965110074], [0.9246314589446478, 0.5399573742579934, 0.48500224969628697], [0.5249895872382662, 0.2512588420155906, 0.757430180462606]]]);
    }
}
