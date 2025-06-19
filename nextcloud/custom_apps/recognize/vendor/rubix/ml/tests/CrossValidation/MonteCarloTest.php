<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\CrossValidation;

use OCA\Recognize\Vendor\Rubix\ML\Parallel;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Validator;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\MonteCarlo;
use OCA\Recognize\Vendor\Rubix\ML\Classifiers\GaussianNB;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Agglomerate;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\Accuracy;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Validators
 * @covers \OCA\Recognize\Vendor\Rubix\ML\CrossValidation\MonteCarlo
 * @internal
 */
class MonteCarloTest extends TestCase
{
    protected const DATASET_SIZE = 50;
    /**
     * @var Agglomerate
     */
    protected $generator;
    /**
     * @var GaussianNB
     */
    protected $estimator;
    /**
     * @var MonteCarlo
     */
    protected $validator;
    /**
     * @var Accuracy
     */
    protected $metric;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->generator = new Agglomerate(['male' => new Blob([69.2, 195.7, 40.0], [1.0, 3.0, 0.3]), 'female' => new Blob([63.7, 168.5, 38.1], [0.8, 2.5, 0.4])], [0.45, 0.55]);
        $this->estimator = new GaussianNB();
        $this->validator = new MonteCarlo(3, 0.2);
        $this->metric = new Accuracy();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(MonteCarlo::class, $this->validator);
        $this->assertInstanceOf(Validator::class, $this->validator);
        $this->assertInstanceOf(Parallel::class, $this->validator);
    }
    /**
     * @test
     */
    public function test() : void
    {
        [$min, $max] = $this->metric->range()->list();
        $dataset = $this->generator->generate(self::DATASET_SIZE);
        $score = $this->validator->test($this->estimator, $dataset, $this->metric);
        $this->assertThat($score, $this->logicalAnd($this->greaterThanOrEqual($min), $this->lessThanOrEqual($max)));
    }
}
