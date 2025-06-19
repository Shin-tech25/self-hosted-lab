<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\CrossValidation\Metrics;

use OCA\Recognize\Vendor\Rubix\ML\Tuple;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\Metric;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\Informedness;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Metrics
 * @covers \OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\Informedness
 * @internal
 */
class InformednessTest extends TestCase
{
    /**
     * @var Informedness
     */
    protected $metric;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->metric = new Informedness();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Informedness::class, $this->metric);
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
        $expected = [EstimatorType::classifier(), EstimatorType::anomalyDetector()];
        $this->assertEquals($expected, $this->metric->compatibility());
    }
    /**
     * @test
     * @dataProvider scoreProvider
     *
     * @param (string|int)[] $predictions
     * @param (string|int)[] $labels
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
        (yield [['wolf', 'lamb', 'wolf', 'lamb', 'wolf'], ['lamb', 'lamb', 'wolf', 'wolf', 'wolf'], 0.16666666666666652]);
        (yield [['wolf', 'wolf', 'lamb', 'lamb', 'lamb'], ['lamb', 'lamb', 'wolf', 'wolf', 'wolf'], -1.0]);
        (yield [['lamb', 'lamb', 'wolf', 'wolf', 'wolf'], ['lamb', 'lamb', 'wolf', 'wolf', 'wolf'], 1.0]);
        (yield [[0, 1, 0, 1, 0], [0, 0, 0, 1, 0], 0.75]);
        (yield [[0, 0, 0, 1, 0], [0, 0, 0, 1, 0], 1.0]);
        (yield [[1, 1, 1, 0, 1], [0, 0, 0, 1, 0], -1.0]);
    }
}
