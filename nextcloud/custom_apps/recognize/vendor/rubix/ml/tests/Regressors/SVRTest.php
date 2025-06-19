<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Regressors;

use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\Regressors\SVR;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\SVM\Linear;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Hyperplane;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\ZScaleStandardizer;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\RSquared;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Regressors
 * @requires extension svm
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Regressors\SVR
 * @internal
 */
class SVRTest extends TestCase
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
     * @var Hyperplane
     */
    protected $generator;
    /**
     * @var SVR
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
        $this->generator = new Hyperplane([1.0, 5.5, -7, 0.01], 0.0, 1.0);
        $this->estimator = new SVR(1, 1.0E-8, new Linear(), \false, 0.001);
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
        $this->assertInstanceOf(SVR::class, $this->estimator);
        $this->assertInstanceOf(Learner::class, $this->estimator);
        $this->assertInstanceOf(Estimator::class, $this->estimator);
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
        $expected = [DataType::continuous()];
        $this->assertEquals($expected, $this->estimator->compatibility());
    }
    /**
     * @test
     */
    public function trainPredict() : void
    {
        $dataset = $this->generator->generate(self::TRAIN_SIZE + self::TEST_SIZE);
        $dataset->apply(new ZScaleStandardizer());
        $testing = $dataset->randomize()->take(self::TEST_SIZE);
        $this->estimator->train($dataset);
        $this->assertTrue($this->estimator->trained());
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
        $this->estimator->train(Labeled::quick([['bad']]));
    }
    /**
     * @test
     */
    public function predictUntrained() : void
    {
        $this->expectException(RuntimeException::class);
        $this->estimator->predict(Unlabeled::quick([[1.5]]));
    }
}
