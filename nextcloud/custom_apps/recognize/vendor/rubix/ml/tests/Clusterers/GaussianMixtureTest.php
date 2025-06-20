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
use OCA\Recognize\Vendor\Rubix\ML\Clusterers\Seeders\KMC2;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Clusterers\GaussianMixture;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Agglomerate;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\VMeasure;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Clusterers
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Clusterers\GaussianMixture
 * @internal
 */
class GaussianMixtureTest extends TestCase
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
     * @var GaussianMixture
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
        $this->estimator = new GaussianMixture(3, 1.0E-9, 100, 0.001, new KMC2(50));
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
        $this->assertInstanceOf(GaussianMixture::class, $this->estimator);
        $this->assertInstanceOf(Learner::class, $this->estimator);
        $this->assertInstanceOf(Probabilistic::class, $this->estimator);
        $this->assertInstanceOf(Verbose::class, $this->estimator);
        $this->assertInstanceOf(Persistable::class, $this->estimator);
        $this->assertInstanceOf(Estimator::class, $this->estimator);
    }
    /**
     * @test
     */
    public function badK() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new GaussianMixture(0);
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
        $expected = ['k' => 3, 'smoothing' => 1.0E-9, 'epochs' => 100, 'min change' => 0.001, 'seeder' => new KMC2(50)];
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
        $priors = $this->estimator->priors();
        $this->assertIsArray($priors);
        $this->assertCount(3, $priors);
        $this->assertContainsOnly('float', $priors);
        $means = $this->estimator->means();
        $this->assertIsArray($means);
        $this->assertCount(3, $means);
        $this->assertContainsOnly('array', $means);
        $variances = $this->estimator->variances();
        $this->assertIsArray($variances);
        $this->assertCount(3, $variances);
        $this->assertContainsOnly('array', $variances);
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
