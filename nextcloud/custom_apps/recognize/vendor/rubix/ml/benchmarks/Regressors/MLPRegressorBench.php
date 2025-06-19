<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Benchmarks\Regressors;

use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Dense;
use OCA\Recognize\Vendor\Rubix\ML\Regressors\MLPRegressor;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Activation;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Hyperplane;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\ActivationFunctions\ReLU;
/**
 * @Groups({"Regressors"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class MLPRegressorBench
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
     * @var MLPRegressor
     */
    protected $estimator;
    public function setUp() : void
    {
        $generator = new Hyperplane([1, 5.5, -7, 0.01], 0.0);
        $this->training = $generator->generate(self::TRAINING_SIZE);
        $this->testing = $generator->generate(self::TESTING_SIZE);
        $this->estimator = new MLPRegressor([new Dense(100), new Activation(new ReLU())]);
    }
    /**
     * @Subject
     * @Iterations(5)
     * @OutputTimeUnit("seconds", precision=3)
     */
    public function trainPredict() : void
    {
        $this->estimator->train($this->training);
        $this->estimator->predict($this->testing);
    }
}
