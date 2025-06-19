<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Specifications;

use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\Metric;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\EstimatorIncompatibleWithMetric;
use function in_array;
/**
 * @internal
 */
class EstimatorIsCompatibleWithMetric extends Specification
{
    /**
     * The estimator.
     *
     * @var Estimator
     */
    protected Estimator $estimator;
    /**
     * The validation metric.
     *
     * @var Metric
     */
    protected Metric $metric;
    /**
     * Build a specification object with the given arguments.
     *
     * @param Estimator $estimator
     * @param Metric $metric
     * @return self
     */
    public static function with(Estimator $estimator, Metric $metric) : self
    {
        return new self($estimator, $metric);
    }
    /**
     * @param Estimator $estimator
     * @param Metric $metric
     */
    public function __construct(Estimator $estimator, Metric $metric)
    {
        $this->estimator = $estimator;
        $this->metric = $metric;
    }
    /**
     * Perform a check of the specification and throw an exception if invalid.
     *
     * @throws \OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException
     */
    public function check() : void
    {
        if (!in_array($this->estimator->type(), $this->metric->compatibility())) {
            throw new EstimatorIncompatibleWithMetric($this->estimator, $this->metric);
        }
    }
}
