<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests;

use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\Parallel;
use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\CommitteeMachine;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Classifiers\GaussianNB;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Circle;
use OCA\Recognize\Vendor\Rubix\ML\Classifiers\KNearestNeighbors;
use OCA\Recognize\Vendor\Rubix\ML\Classifiers\ClassificationTree;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Agglomerate;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\Accuracy;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group MetaEstimators
 * @covers \OCA\Recognize\Vendor\Rubix\ML\CommitteeMachine
 * @internal
 */
class CommitteeMachineTest extends TestCase
{
    protected const TRAIN_SIZE = 512;
    protected const TEST_SIZE = 256;
    protected const MIN_SCORE = 0.9;
    protected const RANDOM_SEED = 0;
    /**
     * @var Agglomerate
     */
    protected $generator;
    /**
     * @var CommitteeMachine
     */
    protected $estimator;
    /**
     * @var Accuracy
     */
    protected $metric;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new Agglomerate(['inner' => new Circle(0.0, 0.0, 1.0, 0.01), 'middle' => new Circle(0.0, 0.0, 5.0, 0.05), 'outer' => new Circle(0.0, 0.0, 10.0, 0.15)], [3, 3, 4]);
        $this->estimator = new CommitteeMachine([new ClassificationTree(10, 3, 2), new KNearestNeighbors(3), new GaussianNB()], [3, 4, 5]);
        $this->metric = new Accuracy();
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
        $this->assertInstanceOf(CommitteeMachine::class, $this->estimator);
        $this->assertInstanceOf(Learner::class, $this->estimator);
        $this->assertInstanceOf(Parallel::class, $this->estimator);
        $this->assertInstanceOf(Persistable::class, $this->estimator);
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
        $expected = ['experts' => [new ClassificationTree(10, 3, 2), new KNearestNeighbors(3), new GaussianNB()], 'influences' => [0.25, 0.3333333333333333, 0.4166666666666667]];
        $this->assertEquals($expected, $this->estimator->params());
    }
    /**
     * @test
     */
    public function trainPredict() : void
    {
        $training = $this->generator->generate(self::TRAIN_SIZE);
        $testing = $this->generator->generate(self::TEST_SIZE);
        $this->estimator->train($training);
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
