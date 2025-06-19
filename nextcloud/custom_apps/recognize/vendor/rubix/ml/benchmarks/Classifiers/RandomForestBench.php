<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Benchmarks\Classifiers;

use OCA\Recognize\Vendor\Rubix\ML\Classifiers\RandomForest;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Classifiers\ClassificationTree;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Agglomerate;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\IntervalDiscretizer;
/**
 * @Groups({"Classifiers"})
 * @internal
 */
class RandomForestBench
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
     * @var RandomForest
     */
    protected $estimator;
    public function setUpContinuous() : void
    {
        $generator = new Agglomerate(['Iris-setosa' => new Blob([5.0, 3.42, 1.46, 0.24], [0.35, 0.38, 0.17, 0.1]), 'Iris-versicolor' => new Blob([5.94, 2.77, 4.26, 1.33], [0.51, 0.31, 0.47, 0.2]), 'Iris-virginica' => new Blob([6.59, 2.97, 5.55, 2.03], [0.63, 0.32, 0.55, 0.27])]);
        $this->training = $generator->generate(self::TRAINING_SIZE);
        $this->testing = $generator->generate(self::TESTING_SIZE);
        $this->estimator = new RandomForest(new ClassificationTree(30));
    }
    public function setUpCategorical() : void
    {
        $generator = new Agglomerate(['Iris-setosa' => new Blob([5.0, 3.42, 1.46, 0.24], [0.35, 0.38, 0.17, 0.1]), 'Iris-versicolor' => new Blob([5.94, 2.77, 4.26, 1.33], [0.51, 0.31, 0.47, 0.2]), 'Iris-virginica' => new Blob([6.59, 2.97, 5.55, 2.03], [0.63, 0.32, 0.55, 0.27])]);
        $dataset = $generator->generate(self::TRAINING_SIZE + self::TESTING_SIZE)->apply(new IntervalDiscretizer(10));
        $this->testing = $dataset->take(self::TESTING_SIZE);
        $this->training = $dataset;
        $this->estimator = new RandomForest(new ClassificationTree(30));
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
