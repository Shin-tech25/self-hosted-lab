<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Specifications;

use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use function count;
/**
 * @internal
 */
class SamplesAreCompatibleWithEstimator extends Specification
{
    /**
     * The dataset that contains samples under validation.
     *
     * @var Dataset
     */
    protected Dataset $dataset;
    /**
     * The estimator.
     *
     * @var Estimator
     */
    protected Estimator $estimator;
    /**
     * Build a specification object with the given arguments.
     *
     * @param Dataset $dataset
     * @param Estimator $estimator
     * @return self
     */
    public static function with(Dataset $dataset, Estimator $estimator) : self
    {
        return new self($dataset, $estimator);
    }
    /**
     * @param Dataset $dataset
     * @param Estimator $estimator
     */
    public function __construct(Dataset $dataset, Estimator $estimator)
    {
        $this->dataset = $dataset;
        $this->estimator = $estimator;
    }
    /**
     * Perform a check of the specification and throw an exception if invalid.
     *
     * @throws InvalidArgumentException
     */
    public function check() : void
    {
        $compatibility = $this->estimator->compatibility();
        $types = $this->dataset->uniqueTypes();
        $compatible = \array_intersect($types, $compatibility);
        if (count($compatible) < count($types)) {
            $incompatible = \array_diff($types, $compatibility);
            throw new InvalidArgumentException("{$this->estimator} is incompatible with " . \implode(', ', $incompatible) . ' data types.');
        }
    }
}
