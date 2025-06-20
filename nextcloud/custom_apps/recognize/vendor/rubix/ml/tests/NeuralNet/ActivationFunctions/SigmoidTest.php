<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\NeuralNet\ActivationFunctions;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\ActivationFunctions\Sigmoid;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\ActivationFunctions\ActivationFunction;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group ActivationFunctions
 * @covers \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\ActivationFunctions\Sigmoid
 * @internal
 */
class SigmoidTest extends TestCase
{
    /**
     * @var Sigmoid
     */
    protected $activationFn;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->activationFn = new Sigmoid();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Sigmoid::class, $this->activationFn);
        $this->assertInstanceOf(ActivationFunction::class, $this->activationFn);
    }
    /**
     * @test
     * @dataProvider computeProvider
     *
     * @param Matrix $input
     * @param list<list<float>> $expected $expected
     */
    public function activate(Matrix $input, array $expected) : void
    {
        $activations = $this->activationFn->activate($input)->asArray();
        $this->assertEquals($expected, $activations);
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function computeProvider() : Generator
    {
        (yield [Matrix::quick([[1.0, -0.5, 0.0, 20.0, -10.0]]), [[0.7310585786300049, 0.3775406687981454, 0.5, 0.9999999979388463, 4.5397868702434395E-5]]]);
        (yield [Matrix::quick([[-0.12, 0.31, -0.49], [0.99, 0.08, -0.03], [0.05, -0.52, 0.54]]), [[0.4700359482354282, 0.5768852611320463, 0.3798935676569099], [0.7290879223493065, 0.5199893401555818, 0.4925005624493796], [0.5124973964842103, 0.3728522336868044, 0.6318124177361016]]]);
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
        $this->assertEquals($expected, $derivatives);
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function differentiateProvider() : Generator
    {
        (yield [Matrix::quick([[1.0, -0.5, 0.0, 20.0, -10.0]]), Matrix::quick([[0.7310585786300049, 0.3775406687981454, 0.5, 0.9999999979388463, 4.5397868702434395E-5]]), [[0.19661193324148185, 0.2350037122015945, 0.25, 2.0611536879193953E-9, 4.5395807735951673E-5]]]);
        (yield [Matrix::quick([[-0.12, 0.31, -0.49], [0.99, 0.08, -0.03], [0.05, -0.52, 0.54]]), Matrix::quick([[0.4700359482354282, 0.5768852611320463, 0.3798935676569099], [0.7290879223493065, 0.5199893401555818, 0.4925005624493796], [0.5124973964842103, 0.3728522336868044, 0.6318124177361016]]), [[0.2491021556018501, 0.24408865662065704, 0.2355744449098147], [0.1975187238336781, 0.24960042628014445, 0.24994375843642433], [0.24984381508111644, 0.23383344552156501, 0.23262548653056345]]]);
    }
}
