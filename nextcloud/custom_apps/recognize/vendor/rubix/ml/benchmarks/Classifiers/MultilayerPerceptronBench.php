<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Benchmarks\Classifiers;

use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Dense;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Activation;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Agglomerate;
use OCA\Recognize\Vendor\Rubix\ML\Classifiers\MultilayerPerceptron;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\ActivationFunctions\ReLU;
/**
 * @Groups({"Classifiers"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class MultilayerPerceptronBench
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
     * @var MultilayerPerceptron
     */
    protected $estimator;
    public function setUp() : void
    {
        $generator = new Agglomerate(['Iris-setosa' => new Blob([5.0, 3.42, 1.46, 0.24], [0.35, 0.38, 0.17, 0.1]), 'Iris-virginica' => new Blob([6.59, 2.97, 5.55, 2.03], [0.63, 0.32, 0.55, 0.27])]);
        $this->training = $generator->generate(self::TRAINING_SIZE);
        $this->testing = $generator->generate(self::TESTING_SIZE);
        $this->estimator = new MultilayerPerceptron([new Dense(100), new Activation(new ReLU())]);
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
