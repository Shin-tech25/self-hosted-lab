<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\NeuralNet\ActivationFunctions;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\ActivationFunctions\ELU;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\ActivationFunctions\ActivationFunction;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use Generator;
/**
 * @group ActivationFunctions
 * @covers \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\ActivationFunctions\ELU
 * @internal
 */
class ELUTest extends TestCase
{
    /**
     * @var ELU
     */
    protected $activationFn;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->activationFn = new ELU(1.0);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(ELU::class, $this->activationFn);
        $this->assertInstanceOf(ActivationFunction::class, $this->activationFn);
    }
    /**
     * @test
     */
    public function badAlpha() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new ELU(-346);
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
        (yield [Matrix::quick([[1.0, -0.5, 0.0, 20.0, -10.0]]), [[1.0, -0.3934693402873666, 0.0, 20.0, -0.9999546000702375]]]);
        (yield [Matrix::quick([[-0.12, 0.31, -0.49], [0.99, 0.08, -0.03], [0.05, -0.52, 0.54]]), [[-0.11307956328284252, 0.31, -0.3873736058155839], [0.99, 0.08, -0.029554466451491845], [0.05, -0.4054794520298056, 0.54]]]);
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
        (yield [Matrix::quick([[1.0, -0.5, 0.0, 20.0, -10.0]]), Matrix::quick([[1.0, -0.3934693402873666, 0.0, 20.0, -0.9999546000702375]]), [[1.0, 0.6065306597126334, 1.0, 1.0, 4.539992976249074E-5]]]);
        (yield [Matrix::quick([[-0.12, 0.31, -0.49], [0.99, 0.08, -0.03], [0.05, -0.52, 0.54]]), Matrix::quick([[-0.11307956328284252, 0.31, -0.3873736058155839], [0.99, 0.08, -0.029554466451491845], [0.05, -0.4054794520298056, 0.54]]), [[0.8869204367171575, 1.0, 0.6126263941844161], [1.0, 1.0, 0.9704455335485082], [1.0, 0.5945205479701944, 1.0]]]);
    }
}
