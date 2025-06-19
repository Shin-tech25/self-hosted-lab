<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Kernels\Distance;

use OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\NaNSafe;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\Distance;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\SafeEuclidean;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Distances
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\SafeEuclidean
 * @internal
 */
class SafeEuclideanTest extends TestCase
{
    /**
     * @var SafeEuclidean
     */
    protected $kernel;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->kernel = new SafeEuclidean();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(SafeEuclidean::class, $this->kernel);
        $this->assertInstanceOf(NaNSafe::class, $this->kernel);
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
        (yield [[2, 1, 4, \NAN], [-2, 1, 8, -2], 6.531972647421808]);
        (yield [[7.4, -2.5, 0.001], [\NAN, -1, 0.075], 1.8393515161599752]);
        (yield [[1000, \NAN, 3000], [1000, \NAN, 3000], 0.0]);
    }
}
