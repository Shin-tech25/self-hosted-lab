<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Backends\Tasks;

use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\Metric;
/**
 * Train and Validate
 *
 * A routine to train using a training set and subsequently cross-validate the model using a
 * testing set and scoring metric.
 *
 * @internal
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
class TrainAndValidate extends Task
{
    /**
     * Train the learner and then return its validation score.
     *
     * @param Learner $estimator
     * @param Dataset $training
     * @param Labeled $testing
     * @param Metric $metric
     * @return float
     */
    public static function score(Learner $estimator, Dataset $training, Labeled $testing, Metric $metric) : float
    {
        $estimator->train($training);
        $predictions = $estimator->predict($testing);
        $score = $metric->score($predictions, $testing->labels());
        return $score;
    }
    /**
     * @param Learner $estimator
     * @param Dataset $training
     * @param Labeled $testing
     * @param Metric $metric
     */
    public function __construct(Learner $estimator, Dataset $training, Labeled $testing, Metric $metric)
    {
        parent::__construct([self::class, 'score'], [$estimator, $training, $testing, $metric]);
    }
}
