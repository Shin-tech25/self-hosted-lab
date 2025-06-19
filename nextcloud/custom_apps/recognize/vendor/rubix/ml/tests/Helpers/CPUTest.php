<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Helpers;

use OCA\Recognize\Vendor\Rubix\ML\Helpers\CPU;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Helpers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Helpers\CPU
 * @internal
 */
class CPUTest extends TestCase
{
    /**
     * @test
     */
    public function epsilon() : void
    {
        $epsilon = CPU::epsilon();
        $this->assertLessThan(1.0, $epsilon);
        $this->assertGreaterThan(0.0, $epsilon);
        $this->assertFalse(1.0 + $epsilon === 1.0);
    }
}
