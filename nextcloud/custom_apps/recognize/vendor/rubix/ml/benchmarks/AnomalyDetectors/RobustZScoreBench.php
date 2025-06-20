<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Benchmarks\AnomalyDetectors;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\AnomalyDetectors\RobustZScore;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Agglomerate;
/**
 * @Groups({"AnomalyDetectors"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class RobustZScoreBench
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
     * @var RobustZScore
     */
    protected $estimator;
    public function setUp() : void
    {
        $generator = new Agglomerate(['Iris-virginica' => new Blob([6.59, 2.97, 5.55, 2.03], [0.63, 0.32, 0.55, 0.27]), 'Iris-versicolor' => new Blob([5.94, 2.77, 4.26, 1.33], [0.51, 0.31, 0.47, 0.2])], [0.99, 0.01]);
        $this->training = $generator->generate(self::TRAINING_SIZE);
        $this->testing = $generator->generate(self::TESTING_SIZE);
        $this->estimator = new RobustZScore();
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
