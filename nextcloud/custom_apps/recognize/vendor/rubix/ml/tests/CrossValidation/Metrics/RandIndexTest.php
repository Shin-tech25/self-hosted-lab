<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\CrossValidation\Metrics;

use OCA\Recognize\Vendor\Rubix\ML\Tuple;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\Metric;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\RandIndex;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Metrics
 * @covers \OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\RandIndex
 * @internal
 */
class RandIndexTest extends TestCase
{
    /**
     * @var RandIndex
     */
    protected $metric;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->metric = new RandIndex();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(RandIndex::class, $this->metric);
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
        $expected = [EstimatorType::clusterer()];
        $this->assertEquals($expected, $this->metric->compatibility());
    }
    /**
     * @test
     * @dataProvider scoreProvider
     *
     * @param (int|string)[] $predictions
     * @param (int|string)[] $labels
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
        (yield [[0, 1, 1, 0, 1], ['lamb', 'lamb', 'wolf', 'wolf', 'wolf'], -0.25000000000000006]);
        (yield [[0, 0, 1, 1, 1], ['lamb', 'lamb', 'wolf', 'wolf', 'wolf'], 1.0]);
        (yield [[1, 1, 0, 0, 0], ['lamb', 'lamb', 'wolf', 'wolf', 'wolf'], 1.0]);
        (yield [[0, 1, 2, 3, 4], ['lamb', 'lamb', 'wolf', 'wolf', 'wolf'], 0.0]);
        (yield [[0, 0, 0, 0, 0], ['lamb', 'lamb', 'wolf', 'wolf', 'wolf'], 0.0]);
    }
}
