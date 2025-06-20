<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Kernels\Distance;

use OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\Hamming;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\Distance;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Distances
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\Hamming
 * @internal
 */
class HammingTest extends TestCase
{
    /**
     * @var Hamming
     */
    protected $kernel;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->kernel = new Hamming();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Hamming::class, $this->kernel);
        $this->assertInstanceOf(Distance::class, $this->kernel);
    }
    /**
     * @test
     * @dataProvider computeProvider
     *
     * @param string[] $a
     * @param string[] $b
     * @param float $expected
     */
    public function compute(array $a, array $b, $expected) : void
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
        (yield [['soup', 'turkey', 'broccoli', 'cake'], ['salad', 'turkey', 'broccoli', 'pie'], 2.0]);
        (yield [['salad', 'ham', 'peas', 'jello'], ['bread', 'steak', 'potato', 'cake'], 4.0]);
        (yield [['toast', 'eggs', 'bacon'], ['toast', 'eggs', 'bacon'], 0.0]);
    }
}
