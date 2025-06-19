<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\NeuralNet\Initializers;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Initializers\Uniform;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Initializers\Initializer;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Initializers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Initializers\Uniform
 * @internal
 */
class UniformTest extends TestCase
{
    /**
     * @var Uniform
     */
    protected $initializer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->initializer = new Uniform(0.05);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Uniform::class, $this->initializer);
        $this->assertInstanceOf(Initializer::class, $this->initializer);
    }
    /**
     * @test
     */
    public function initialize() : void
    {
        $w = $this->initializer->initialize(4, 3);
        $this->assertInstanceOf(Matrix::class, $w);
        $this->assertEquals([3, 4], $w->shape());
    }
}
