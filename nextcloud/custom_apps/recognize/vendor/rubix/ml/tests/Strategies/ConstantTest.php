<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Strategies;

use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Strategies\Constant;
use OCA\Recognize\Vendor\Rubix\ML\Strategies\Strategy;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Strategies
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Strategies\Constant
 * @internal
 */
class ConstantTest extends TestCase
{
    /**
     * @var Constant
     */
    protected $strategy;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->strategy = new Constant(42);
    }
    protected function assertPreConditions() : void
    {
        $this->assertTrue($this->strategy->fitted());
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Constant::class, $this->strategy);
        $this->assertInstanceOf(Strategy::class, $this->strategy);
    }
    /**
     * @test
     */
    public function type() : void
    {
        $this->assertEquals(DataType::continuous(), $this->strategy->type());
    }
    /**
     * @test
     */
    public function fitGuess() : void
    {
        $this->strategy->fit([]);
        $this->assertTrue($this->strategy->fitted());
        $guess = $this->strategy->guess();
        $this->assertEquals(42, $guess);
    }
}
