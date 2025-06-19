<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Strategies;

use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Strategies\Mean;
use OCA\Recognize\Vendor\Rubix\ML\Strategies\Strategy;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Strategies
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Strategies\Mean
 * @internal
 */
class MeanTest extends TestCase
{
    /**
     * @var Mean
     */
    protected $strategy;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->strategy = new Mean();
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
        $this->assertInstanceOf(Mean::class, $this->strategy);
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
        $this->strategy->fit([1, 2, 3, 4, 5]);
        $this->assertTrue($this->strategy->fitted());
        $guess = $this->strategy->guess();
        $this->assertEquals(3.0, $guess);
    }
}
