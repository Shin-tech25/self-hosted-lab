<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests;

use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\Probabilistic;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\PersistentModel;
use OCA\Recognize\Vendor\Rubix\ML\Serializers\RBX;
use OCA\Recognize\Vendor\Rubix\ML\Persisters\Filesystem;
use OCA\Recognize\Vendor\Rubix\ML\AnomalyDetectors\Scoring;
use OCA\Recognize\Vendor\Rubix\ML\Classifiers\GaussianNB;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group MetaEstimators
 * @covers \OCA\Recognize\Vendor\Rubix\ML\PersistentModel
 * @internal
 */
class PersistentModelTest extends TestCase
{
    /**
     * @var PersistentModel
     */
    protected $estimator;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->estimator = new PersistentModel(new GaussianNB(), new Filesystem('test.model'), new RBX());
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(PersistentModel::class, $this->estimator);
        $this->assertInstanceOf(Probabilistic::class, $this->estimator);
        $this->assertInstanceOf(Scoring::class, $this->estimator);
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
        $this->assertEquals([DataType::continuous()], $this->estimator->compatibility());
    }
    /**
     * @test
     */
    public function params() : void
    {
        $expected = ['base' => new GaussianNB(), 'persister' => new Filesystem('test.model'), 'serializer' => new RBX()];
        $this->assertEquals($expected, $this->estimator->params());
    }
}
