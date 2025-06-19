<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\NeuralNet\Initializers;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Initializers\He;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Initializers\Initializer;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Initializers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Initializers\He
 * @internal
 */
class HeTest extends TestCase
{
    /**
     * @var He
     */
    protected $initializer;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->initializer = new He();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(He::class, $this->initializer);
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
