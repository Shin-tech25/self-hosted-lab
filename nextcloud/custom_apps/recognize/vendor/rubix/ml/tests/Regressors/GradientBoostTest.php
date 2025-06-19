<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Regressors;

use OCA\Recognize\Vendor\Rubix\ML\Verbose;
use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\RanksFeatures;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\Regressors\Ridge;
use OCA\Recognize\Vendor\Rubix\ML\Loggers\BlackHole;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Regressors\GradientBoost;
use OCA\Recognize\Vendor\Rubix\ML\Regressors\RegressionTree;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\RMSE;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\SwissRoll;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\RSquared;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Regressors
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Regressors\GradientBoost
 * @internal
 */
class GradientBoostTest extends TestCase
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
     * @var SwissRoll
     */
    protected $generator;
    /**
     * @var GradientBoost
     */
    protected $estimator;
    /**
     * @var RSquared
     */
    protected $metric;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new SwissRoll(4.0, -7.0, 0.0, 1.0, 21.0, 0.5);
        $this->estimator = new GradientBoost(new RegressionTree(3), 0.1, 0.3, 300, 0.0001, 10, 0.1, new RMSE());
        $this->metric = new RSquared();
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
        $this->assertInstanceOf(GradientBoost::class, $this->estimator);
        $this->assertInstanceOf(Estimator::class, $this->estimator);
        $this->assertInstanceOf(Learner::class, $this->estimator);
        $this->assertInstanceOf(Verbose::class, $this->estimator);
        $this->assertInstanceOf(RanksFeatures::class, $this->estimator);
        $this->assertInstanceOf(Persistable::class, $this->estimator);
    }
    /**
     * @test
     */
    public function incompatibleBooster() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new GradientBoost(new Ridge());
    }
    /**
     * @test
     */
    public function badLearningRate() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new GradientBoost(null, -0.001);
    }
    /**
     * @test
     */
    public function type() : void
    {
        $this->assertEquals(EstimatorType::regressor(), $this->estimator->type());
    }
    /**
     * @test
     */
    public function compatibility() : void
    {
        $expected = [DataType::categorical(), DataType::continuous()];
        $this->assertEquals($expected, $this->estimator->compatibility());
    }
    /**
     * @test
     */
    public function params() : void
    {
        $expected = ['booster' => new RegressionTree(3), 'rate' => 0.1, 'ratio' => 0.3, 'epochs' => 300, 'min change' => 0.0001, 'window' => 10, 'hold out' => 0.1, 'metric' => new RMSE()];
        $this->assertEquals($expected, $this->estimator->params());
    }
    /**
     * @test
     */
    public function trainPredictImportances() : void
    {
        $this->estimator->setLogger(new BlackHole());
        $training = $this->generator->generate(self::TRAIN_SIZE);
        $testing = $this->generator->generate(self::TEST_SIZE);
        $this->estimator->train($training);
        $this->assertTrue($this->estimator->trained());
        $losses = $this->estimator->losses();
        $this->assertIsArray($losses);
        $this->assertContainsOnly('float', $losses);
        $scores = $this->estimator->scores();
        $this->assertIsArray($scores);
        $this->assertContainsOnly('float', $scores);
        $importances = $this->estimator->featureImportances();
        $this->assertIsArray($importances);
        $this->assertCount(3, $importances);
        $this->assertContainsOnly('float', $importances);
        $predictions = $this->estimator->predict($testing);
        $score = $this->metric->score($predictions, $testing->labels());
        $this->assertGreaterThanOrEqual(self::MIN_SCORE, $score);
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
