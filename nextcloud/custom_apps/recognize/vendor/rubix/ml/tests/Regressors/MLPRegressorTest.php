<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Regressors;

use OCA\Recognize\Vendor\Rubix\ML\Online;
use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\Verbose;
use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Encoding;
use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\Helpers\Graphviz;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
use OCA\Recognize\Vendor\Rubix\ML\Loggers\BlackHole;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Persisters\Filesystem;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Dense;
use OCA\Recognize\Vendor\Rubix\ML\Regressors\MLPRegressor;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\Adam;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Activation;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\RMSE;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\SwissRoll;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\ZScaleStandardizer;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\RSquared;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\ActivationFunctions\SiLU;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\CostFunctions\LeastSquares;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Regressors
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Regressors\MLPRegressor
 * @internal
 */
class MLPRegressorTest extends TestCase
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
     * @var MLPRegressor
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
        $this->estimator = new MLPRegressor([new Dense(32), new Activation(new SiLU()), new Dense(16), new Activation(new SiLU()), new Dense(8), new Activation(new SiLU())], 32, new Adam(0.01), 0.0001, 100, 0.0001, 5, 0.1, new LeastSquares(), new RMSE());
        $this->metric = new RSquared();
        $this->estimator->setLogger(new BlackHole());
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
        $this->assertInstanceOf(MLPRegressor::class, $this->estimator);
        $this->assertInstanceOf(Online::class, $this->estimator);
        $this->assertInstanceOf(Learner::class, $this->estimator);
        $this->assertInstanceOf(Verbose::class, $this->estimator);
        $this->assertInstanceOf(Persistable::class, $this->estimator);
        $this->assertInstanceOf(Estimator::class, $this->estimator);
    }
    /**
     * @test
     */
    public function badBatchSize() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new MLPRegressor([], -100);
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
    public function params() : void
    {
        $expected = ['hidden layers' => [new Dense(32), new Activation(new SiLU()), new Dense(16), new Activation(new SiLU()), new Dense(8), new Activation(new SiLU())], 'batch size' => 32, 'optimizer' => new Adam(0.01), 'l2 penalty' => 0.0001, 'epochs' => 100, 'min change' => 0.0001, 'window' => 5, 'hold out' => 0.1, 'cost fn' => new LeastSquares(), 'metric' => new RMSE()];
        $this->assertEquals($expected, $this->estimator->params());
    }
    /**
     * @test
     */
    public function trainPartialPredict() : void
    {
        $dataset = $this->generator->generate(self::TRAIN_SIZE + self::TEST_SIZE);
        $dataset->apply(new ZScaleStandardizer());
        $testing = $dataset->randomize()->take(self::TEST_SIZE);
        $folds = $dataset->fold(3);
        $this->estimator->train($folds[0]);
        $this->estimator->partial($folds[1]);
        $this->estimator->partial($folds[2]);
        $this->assertTrue($this->estimator->trained());
        $dot = $this->estimator->exportGraphviz();
        // Graphviz::dotToImage($dot)->saveTo(new Filesystem('test.png'));
        $this->assertInstanceOf(Encoding::class, $dot);
        $this->assertStringStartsWith('digraph Tree {', $dot);
        $losses = $this->estimator->losses();
        $this->assertIsArray($losses);
        $this->assertContainsOnly('float', $losses);
        $scores = $this->estimator->scores();
        $this->assertIsArray($scores);
        $this->assertContainsOnly('float', $scores);
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
        $this->estimator->train(Labeled::quick([['bad']], [2]));
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
