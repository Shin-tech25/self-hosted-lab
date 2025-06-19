<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Strategies;

use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Strategies\Prior;
use OCA\Recognize\Vendor\Rubix\ML\Strategies\Strategy;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Strategies
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Strategies\Prior
 * @internal
 */
class PriorTest extends TestCase
{
    /**
     * @var Prior
     */
    protected $strategy;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->strategy = new Prior();
    }
    protected function assertPreConditions() : void
    {
        $this->assertFalse($this->strategy->fitted());
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Prior::class, $this->strategy);
        $this->assertInstanceOf(Strategy::class, $this->strategy);
    }
    /**
     * @test
     */
    public function type() : void
    {
        $this->assertEquals(DataType::categorical(), $this->strategy->type());
    }
    /**
     * @test
     */
    public function fitGuess() : void
    {
        $values = ['a', 'a', 'b', 'a', 'c'];
        $this->strategy->fit($values);
        $this->assertTrue($this->strategy->fitted());
        $value = $this->strategy->guess();
        $this->assertContains($value, $values);
    }
}
