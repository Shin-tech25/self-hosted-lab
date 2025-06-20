<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\NeuralNet\ActivationFunctions;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\ActivationFunctions\HyperbolicTangent;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\ActivationFunctions\ActivationFunction;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group ActivationFunctions
 * @covers \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\ActivationFunctions\HyperbolicTangent
 * @internal
 */
class HyperbolicTangentTest extends TestCase
{
    /**
     * @var HyperbolicTangent
     */
    protected $activationFn;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->activationFn = new HyperbolicTangent();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(HyperbolicTangent::class, $this->activationFn);
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
        $this->assertEqualsWithDelta($expected, $activations, 1.0E-8);
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function computeProvider() : Generator
    {
        (yield [Matrix::quick([[1.0, -0.5, 0.0, 20.0, -10.0]]), [[0.7615941559557649, -0.46211715726000974, 0.0, 1.0, -0.9999999958776927]]]);
        (yield [Matrix::quick([[-0.12, 0.31, -0.49], [0.99, 0.08, -0.03], [0.05, -0.52, 0.54]]), [[-0.11942729853438588, 0.3004370971476541, -0.4542164326822591], [0.7573623242165263, 0.07982976911113136, -0.029991003238820143], [0.04995837495787998, -0.477700012168498, 0.49298796667532435]]]);
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
        (yield [Matrix::quick([[1.0, -0.5, 0.0, 20.0, -10.0]]), Matrix::quick([[0.7615941559557649, -0.46211715726000974, 0.0, 1.0, -0.9999999958776927]]), [[0.41997434161402614, 0.7864477329659274, 1.0, 0.0, 8.244614546626394E-9]]]);
        (yield [Matrix::quick([[-0.12, 0.31, -0.49], [0.99, 0.08, -0.03], [0.05, -0.52, 0.54]]), Matrix::quick([[-0.11942729853438588, 0.3004370971476541, -0.4542164326822591], [0.7573623242165263, 0.07982976911113136, -0.029991003238820143], [0.04995837495787998, -0.477700012168498, 0.49298796667532435]]), [[0.9857371203647787, 0.9097375506574911, 0.7936874322814028], [0.4264023098573413, 0.9936272079636634, 0.9991005397247291], [0.9975041607715679, 0.7718026983742169, 0.7569628647133293]]]);
    }
}
