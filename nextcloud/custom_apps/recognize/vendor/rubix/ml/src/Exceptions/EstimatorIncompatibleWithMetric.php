<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Exceptions;

use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\Metric;
/** @internal */
class EstimatorIncompatibleWithMetric extends InvalidArgumentException
{
    /**
     * @param Estimator $estimator
     * @param Metric $metric
     */
    public function __construct(Estimator $estimator, Metric $metric)
    {
        parent::__construct("{$metric} is not compatible with {$estimator->type()}s.");
    }
}
