<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\CrossValidation\Metrics;

use OCA\Recognize\Vendor\Rubix\ML\Tuple;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\Metric;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\MeanAbsoluteError;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Metrics
 * @covers \OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\MeanAbsoluteError
 * @internal
 */
class MeanAbsoluteErrorTest extends TestCase
{
    /**
     * @var MeanAbsoluteError
     */
    protected $metric;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->metric = new MeanAbsoluteError();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(MeanAbsoluteError::class, $this->metric);
        $this->assertInstanceOf(Metric::class, $this->metric);
    }
    /**
     * @test
     */
    public function range() : void
    {
        $tuple = $this->metric->range();
        $this->assertInstanceOf(Tuple::class, $tuple);
        $this->assertCount(2, $tuple);
        $this->assertGreaterThan($tuple[0], $tuple[1]);
    }
    /**
     * @test
     */
    public function compatibility() : void
    {
        $expected = [EstimatorType::regressor()];
        $this->assertEquals($expected, $this->metric->compatibility());
    }
    /**
     * @test
     * @dataProvider scoreProvider
     *
     * @param (int|float)[] $predictions
     * @param (int|float)[] $labels
     * @param float $expected
     */
    public function score(array $predictions, array $labels, float $expected) : void
    {
        [$min, $max] = $this->metric->range()->list();
        $score = $this->metric->score($predictions, $labels);
        $this->assertThat($score, $this->logicalAnd($this->greaterThanOrEqual($min), $this->lessThanOrEqual($max)));
        $this->assertEquals($expected, $score);
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function scoreProvider() : Generator
    {
        (yield [[7, 9.5, -20, -500, 0.079], [10, 10.0, 6, -1400, 0.08], -185.90019999999998]);
        (yield [[0, 0, 0, 0, 0], [10, 10.0, 6, -1400, 0.08], -285.216]);
        (yield [[10, 10.0, 6, -1400, 0.08], [10, 10.0, 6, -1400, 0.08], 0.0]);
    }
}
