<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Classifiers;

use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\Classifiers\SVC;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\SVM\RBF;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\ZScaleStandardizer;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Agglomerate;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\FBeta;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Classifiers
 * @requires extension svm
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Classifiers\SVC
 * @internal
 */
class SVCTest extends TestCase
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
     * @var SVC
     */
    protected $estimator;
    /**
     * @var FBeta
     */
    protected $metric;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new Agglomerate(['male' => new Blob([69.2, 195.7, 40.0], [2.0, 6.0, 0.6]), 'female' => new Blob([63.7, 168.5, 38.1], [1.6, 5.0, 0.8])], [0.45, 0.55]);
        $this->estimator = new SVC(1.0, new RBF(), \true, 0.001);
        $this->metric = new FBeta();
        \srand(self::RANDOM_SEED);
    }
    protected function assertPreConditions() : void
    {
        $this->assertFalse($this->estimator->trained());
    }
    /**
     * @after
     */
    protected function tearDown() : void
    {
        if (\file_exists('svc.model')) {
            \unlink('svc.model');
        }
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(SVC::class, $this->estimator);
        $this->assertInstanceOf(Learner::class, $this->estimator);
        $this->assertInstanceOf(Estimator::class, $this->estimator);
    }
    /**
     * @test
     */
    public function type() : void
    {
        $this->assertEquals(EstimatorType::classifier(), $this->estimator->type());
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
        $expected = ['c' => 1.0, 'kernel' => new RBF(), 'shrinking' => \true, 'tolerance' => 0.001, 'cache size' => 100.0];
        $this->assertEquals($expected, $this->estimator->params());
    }
    /**
     * @test
     */
    public function trainSaveLoadPredict() : void
    {
        $dataset = $this->generator->generate(self::TRAIN_SIZE + self::TEST_SIZE);
        $dataset->apply(new ZScaleStandardizer());
        $testing = $dataset->randomize()->take(self::TEST_SIZE);
        $this->estimator->train($dataset);
        $this->assertTrue($this->estimator->trained());
        $this->estimator->save('svc.model');
        $this->estimator->load('svc.model');
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
