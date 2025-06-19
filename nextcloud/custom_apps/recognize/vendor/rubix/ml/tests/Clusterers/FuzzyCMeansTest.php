<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Clusterers;

use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\Verbose;
use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\Probabilistic;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\Loggers\BlackHole;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Clusterers\FuzzyCMeans;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Clusterers\Seeders\Random;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\Euclidean;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Agglomerate;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\VMeasure;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Clusterers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Clusterers\FuzzyCMeans
 * @internal
 */
class FuzzyCMeansTest extends TestCase
{
    /**
     * The number of samples in the training set.
     *
     * @var int
     */
    protected const TRAIN_SIZE = 512;
    /**
     * The number of samples in the validation set.
     *
     * @var int
     */
    protected const TEST_SIZE = 256;
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
     * @var FuzzyCMeans
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
        $this->generator = new Agglomerate(['red' => new Blob([255, 32, 0], 50.0), 'green' => new Blob([0, 128, 0], 10.0), 'blue' => new Blob([0, 32, 255], 30.0)], [0.5, 0.2, 0.3]);
        $this->estimator = new FuzzyCMeans(3, 2.0, 300, 0.0001, new Euclidean(), new Random());
        $this->metric = new VMeasure();
        \srand(self::RANDOM_SEED);
    }
    protected function assertPreConditions() : void
    {
        $this->assertFalse($this->estimator->trained());
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(FuzzyCMeans::class, $this->estimator);
        $this->assertInstanceOf(Learner::class, $this->estimator);
        $this->assertInstanceOf(Probabilistic::class, $this->estimator);
        $this->assertInstanceOf(Verbose::class, $this->estimator);
        $this->assertInstanceOf(Persistable::class, $this->estimator);
        $this->assertInstanceOf(Estimator::class, $this->estimator);
    }
    /**
     * @test
     */
    public function badC() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new FuzzyCMeans(0);
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
        $expected = ['c' => 3, 'fuzz' => 2.0, 'epochs' => 300, 'min change' => 0.0001, 'kernel' => new Euclidean(), 'seeder' => new Random()];
        $this->assertEquals($expected, $this->estimator->params());
    }
    /**
     * @test
     */
    public function trainPredict() : void
    {
        $this->estimator->setLogger(new BlackHole());
        $training = $this->generator->generate(self::TRAIN_SIZE);
        $testing = $this->generator->generate(self::TEST_SIZE);
        $this->estimator->train($training);
        $this->assertTrue($this->estimator->trained());
        $centroids = $this->estimator->centroids();
        $this->assertIsArray($centroids);
        $this->assertCount(3, $centroids);
        $this->assertContainsOnly('array', $centroids);
        $losses = $this->estimator->losses();
        $this->assertIsArray($losses);
        $this->assertContainsOnly('float', $losses);
        $predictions = $this->estimator->predict($testing);
        $score = $this->metric->score($predictions, $testing->labels());
        $this->assertGreaterThanOrEqual(self::MIN_SCORE, $score);
    }
    /**
     * @test
     */
    public function trainIncompatible() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->estimator->train(Unlabeled::quick([['bad']]));
    }
    /**
     * @test
     */
    public function predictUntrained() : void
    {
        $this->expectException(RuntimeException::class);
        $this->estimator->predict(Unlabeled::quick());
    }
}
