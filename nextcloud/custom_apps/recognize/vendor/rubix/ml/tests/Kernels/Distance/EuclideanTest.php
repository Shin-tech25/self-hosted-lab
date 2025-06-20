<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Kernels\Distance;

use OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\Euclidean;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\Distance;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Distances
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\Euclidean
 * @internal
 */
class EuclideanTest extends TestCase
{
    /**
     * @var Euclidean
     */
    protected $kernel;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->kernel = new Euclidean();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Euclidean::class, $this->kernel);
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
        (yield [[2, 1, 4, 0], [-2, 1, 8, -2], 6.0]);
        (yield [[7.4, -2.5], [0.01, -1], 7.540696254325591]);
        (yield [[1000, -2000, 3000], [1000, -2000, 3000], 0.0]);
    }
}
