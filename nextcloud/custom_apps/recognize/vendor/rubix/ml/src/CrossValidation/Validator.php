<?php

namespace OCA\Recognize\Vendor\Rubix\ML\CrossValidation;

use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\Metric;
use Stringable;
/** @internal */
interface Validator extends Stringable
{
    /**
     * Test the estimator with the supplied dataset and return a validation score.
     *
     * @param Learner $estimator
     * @param Labeled $dataset
     * @param Metric $metric
     * @return float
     */
    public function test(Learner $estimator, Labeled $dataset, Metric $metric) : float;
}
