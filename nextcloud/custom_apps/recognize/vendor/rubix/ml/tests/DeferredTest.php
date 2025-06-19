<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests;

use OCA\Recognize\Vendor\Rubix\ML\Deferred;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Other
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Deferred
 * @internal
 */
class DeferredTest extends TestCase
{
    /**
     * @var Deferred
     */
    protected $deferred;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->deferred = new Deferred(function ($a, $b) {
            return $a + $b;
        }, [1, 2]);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Deferred::class, $this->deferred);
        $this->assertIsCallable($this->deferred);
    }
    /**
     * @test
     */
    public function compute() : void
    {
        $this->assertEquals(3, $this->deferred->compute());
    }
}
