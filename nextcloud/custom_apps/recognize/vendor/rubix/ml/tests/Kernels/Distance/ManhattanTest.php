<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Kernels\Distance;

use OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\Manhattan;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\Distance;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Distances
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\Manhattan
 * @internal
 */
class ManhattanTest extends TestCase
{
    /**
     * @var Manhattan
     */
    protected $kernel;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->kernel = new Manhattan();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Manhattan::class, $this->kernel);
        $this->assertInstanceOf(Distance::class, $this->kernel);
    }
    /**
     * @test
     * @dataProvider computeProvider
     *
     * @param (int|float)[] $a
     * @param (int|float)[] $b
     * @param float $expected
     */
    public function compute(array $a, array $b, float $expected) : void
    {
        $distance = $this->kernel->compute($a, $b);
        $this->assertGreaterThanOrEqual(0.0, $distance);
        $this->assertEquals($expected, $distance);
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function computeProvider() : Generator
    {
        (yield [[2, 1, 4, 0], [-2, 1, 8, -2], 10.0]);
        (yield [[7.4, -2.5], [0.01, -1], 8.890000000000001]);
        (yield [[1000, -2000, 3000], [1000, -2000, 3000], 0.0]);
    }
}
