<?php

namespace OCA\Recognize\Vendor\Rubix\ML\CrossValidation;

use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\Metric;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\EstimatorIsCompatibleWithMetric;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
/**
 * Hold Out
 *
 * Hold Out is a quick and simple cross validation technique that uses a validation set
 * that is *held out* from the training data. The advantages of Hold Out is that the
 * validation score is quick to compute, however it does not allow the learner to *both*
 * train and test on all the data in the training set.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class HoldOut implements Validator
{
    /**
     * The hold out ratio. i.e. the ratio of samples to use for testing.
     *
     * @var float
     */
    protected float $ratio;
    /**
     * @param float $ratio
     * @throws InvalidArgumentException
     */
    public function __construct(float $ratio = 0.2)
    {
        if ($ratio <= 0.0 or $ratio >= 1.0) {
            throw new InvalidArgumentException('Ratio must be' . " between 0 and 1, {$ratio} given.");
        }
        $this->ratio = $ratio;
    }
    /**
     * Test the estimator with the supplied dataset and return a validation score.
     *
     * @param Learner $estimator
     * @param Labeled $dataset
     * @param Metric $metric
     * @throws RuntimeException
     * @return float
     */
    public function test(Learner $estimator, Labeled $dataset, Metric $metric) : float
    {
        EstimatorIsCompatibleWithMetric::with($estimator, $metric)->check();
        [$testing, $training] = $dataset->labelType()->isCategorical() ? $dataset->stratifiedSplit($this->ratio) : $dataset->randomize()->split($this->ratio);
        if ($testing->empty()) {
            throw new RuntimeException('Dataset does not contain' . ' enough records to create a validation set with a' . " hold out ratio of {$this->ratio}.");
        }
        $estimator->train($training);
        $predictions = $estimator->predict($testing);
        $score = $metric->score($predictions, $testing->labels());
        return $score;
    }
    /**
     * Return the string representation of the object.
     *
     * @internal
     *
     * @return string
     */
    public function __toString() : string
    {
        return "Hold Out (ratio: {$this->ratio})";
    }
}
