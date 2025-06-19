<?php

namespace OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics;

use OCA\Recognize\Vendor\Rubix\ML\Tuple;
use Stringable;
/** @internal */
interface Metric extends Stringable
{
    /**
     * Return a tuple of the min and max score for this metric.
     *
     * @return \OCA\Recognize\Vendor\Rubix\ML\Tuple{float,float}
     */
    public function range() : Tuple;
    /**
     * The estimator types that this metric is compatible with.
     *
     * @internal
     *
     * @return list<\OCA\Recognize\Vendor\Rubix\ML\EstimatorType>
     */
    public function compatibility() : array;
    /**
     * Score a set of predictions and their ground-truth labels.
     *
     * @param list<string|int|float> $predictions
     * @param list<string|int|float> $labels
     * @return float
     */
    public function score(array $predictions, array $labels) : float;
}
