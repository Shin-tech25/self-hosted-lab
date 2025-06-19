<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Clusterers;

use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\Clusterers\DBSCAN;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Graph\Trees\BallTree;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Circle;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Agglomerate;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\VMeasure;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Clusterers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Clusterers\DBSCAN
 * @internal
 */
class DBSCANTest extends TestCase
{
    /**
     * The number of samples in the validation set.
     *
     * @var int
     */
    protected const TEST_SIZE = 512;
    /**
     * The minimum validation score required to pass the test.
     *
     * @var float
     */
    protected const MIN_SCORE = 0.9;
    /**
     * Constant used to see the random number generator.
     *
     * @var int
     */
    protected const RANDOM_SEED = 0;
    /**
     * @var Agglomerate
     */
    protected $generator;
    /**
     * @var DBSCAN
     */
    protected $estimator;
    /**
     * @var VMeasure
     */
    protected $metric;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new Agglomerate(['inner' => new Circle(0.0, 0.0, 1.0, 0.01), 'middle' => new Circle(0.0, 0.0, 5.0, 0.05), 'outer' => new Circle(0.0, 0.0, 10.0, 0.1)]);
        $this->estimator = new DBSCAN(1.2, 20, new BallTree());
        $this->metric = new VMeasure();
        \srand(self::RANDOM_SEED);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(DBSCAN::class, $this->estimator);
        $this->assertInstanceOf(Estimator::class, $this->estimator);
    }
    /**
     * @test
     */
    public function badRadius() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new DBSCAN(0.0);
    }
    /**
     * @test
     */
    public function type() : void
    {
        $this->assertEquals(EstimatorType::clusterer(), $this->estimator->type());
    }
    /**
     * @test
     */
    public function compatibility() : void
    {
        $expected = [DataType::continuous()];
        $this->assertEquals($expected, $this->estimator->compatibility());
    }
    /**
     * @test
     */
    public function params() : void
    {
        $expected = ['radius' => 1.2, 'min density' => 20, 'tree' => new BallTree()];
        $this->assertEquals($expected, $this->estimator->params());
    }
    /**
     * @test
     */
    public function predict() : void
    {
        $testing = $this->generator->generate(self::TEST_SIZE);
        $predictions = $this->estimator->predict($testing);
        $score = $this->metric->score($predictions, $testing->labels());
        $this->assertGreaterThanOrEqual(self::MIN_SCORE, $score);
    }
    /**
     * @test
     */
    public function predictIncompatible() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->estimator->predict(Unlabeled::quick([['bad']]));
    }
}
