<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Benchmarks\Regressors;

use OCA\Recognize\Vendor\Rubix\ML\Regressors\GradientBoost;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Hyperplane;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\IntervalDiscretizer;
/**
 * @Groups({"Regressors"})
 * @internal
 */
class GradientBoostBench
{
    protected const TRAINING_SIZE = 10000;
    protected const TESTING_SIZE = 10000;
    /**
     * @var \OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
     */
    protected $training;
    /**
     * @var \OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
     */
    protected $testing;
    /**
     * @var GradientBoost
     */
    protected $estimator;
    public function setUpContinuous() : void
    {
        $generator = new Hyperplane([1, 5.5, -7, 0.01], 0.0);
        $this->training = $generator->generate(self::TRAINING_SIZE);
        $this->testing = $generator->generate(self::TESTING_SIZE);
        $this->estimator = new GradientBoost();
    }
    public function setUpCategorical() : void
    {
        $generator = new Hyperplane([1, 5.5, -7, 0.01], 0.0);
        $dataset = $generator->generate(self::TRAINING_SIZE + self::TESTING_SIZE)->apply(new IntervalDiscretizer(10));
        $this->testing = $dataset->take(self::TESTING_SIZE);
        $this->training = $dataset;
        $this->estimator = new GradientBoost();
    }
    /**
     * @Subject
     * @Iterations(5)
     * @BeforeMethods({"setUpContinuous"})
     * @OutputTimeUnit("seconds", precision=3)
     */
    public function continuous() : void
    {
        $this->estimator->train($this->training);
        $this->estimator->predict($this->testing);
    }
    /**
     * @Subject
     * @Iterations(5)
     * @BeforeMethods({"setUpCategorical"})
     * @OutputTimeUnit("seconds", precision=3)
     */
    public function categorical() : void
    {
        $this->estimator->train($this->training);
        $this->estimator->predict($this->testing);
    }
}
