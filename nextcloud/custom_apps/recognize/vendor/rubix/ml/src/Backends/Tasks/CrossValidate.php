<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Backends\Tasks;

use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Validator;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\Metric;
/**
 * Cross Validate
 *
 * @internal
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
class CrossValidate extends Task
{
    /**
     * Cross validate a learner with a given dataset and return the score.
     *
     * @param Learner $estimator
     * @param Labeled $dataset
     * @param Validator $validator
     * @param Metric $metric
     * @return float
     */
    public static function score(Learner $estimator, Labeled $dataset, Validator $validator, Metric $metric) : float
    {
        return $validator->test($estimator, $dataset, $metric);
    }
    /**
     * @param Learner $estimator
     * @param Labeled $dataset
     * @param Validator $validator
     * @param Metric $metric
     */
    public function __construct(Learner $estimator, Labeled $dataset, Validator $validator, Metric $metric)
    {
        parent::__construct([self::class, 'score'], [$estimator, $dataset, $validator, $metric]);
    }
}
