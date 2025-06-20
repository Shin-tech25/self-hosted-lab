<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Specifications;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\IncorrectDatasetDimensionality;
/**
 * @internal
 */
class DatasetHasDimensionality extends Specification
{
    /**
     * The dataset that contains samples under validation.
     *
     * @var Dataset
     */
    protected Dataset $dataset;
    /**
     * The target dimensionality.
     *
     * @var int
     */
    protected int $dimensions;
    /**
     * Build a specification object with the given arguments.
     *
     * @param Dataset $dataset
     * @param int $dimensions
     * @return self
     */
    public static function with(Dataset $dataset, int $dimensions) : self
    {
        return new self($dataset, $dimensions);
    }
    /**
     * @param Dataset $dataset
     * @param int $dimensions
     * @throws InvalidArgumentException
     */
    public function __construct(Dataset $dataset, int $dimensions)
    {
        if ($dimensions < 0) {
            throw new InvalidArgumentException('Dimensions must be' . " greater than 0, {$dimensions} given.");
        }
        $this->dataset = $dataset;
        $this->dimensions = $dimensions;
    }
    /**
     * Perform a check of the specification and throw an exception if invalid.
     *
     * @throws IncorrectDatasetDimensionality
     */
    public function check() : void
    {
        if ($this->dataset->numFeatures() !== $this->dimensions) {
            throw new IncorrectDatasetDimensionality($this->dataset, $this->dimensions);
        }
    }
}
