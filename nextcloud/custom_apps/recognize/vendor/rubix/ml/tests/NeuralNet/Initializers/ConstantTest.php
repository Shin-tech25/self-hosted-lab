<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\NeuralNet\Initializers;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Initializers\Constant;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Initializers\Initializer;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Initializers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Initializers\Constant
 * @internal
 */
class ConstantTest extends TestCase
{
    /**
     * @var Constant
     */
    protected $initializer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->initializer = new Constant(4.8);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Constant::class, $this->initializer);
        $this->assertInstanceOf(Initializer::class, $this->initializer);
    }
    /**
     * @test
     */
    public function initialize() : void
    {
        $w = $this->initializer->initialize(4, 3);
        $expected = [[4.8, 4.8, 4.8, 4.8], [4.8, 4.8, 4.8, 4.8], [4.8, 4.8, 4.8, 4.8]];
        $this->assertInstanceOf(Matrix::class, $w);
        $this->assertEquals([3, 4], $w->shape());
        $this->assertEquals($expected, $w->asArray());
    }
}
