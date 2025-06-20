<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Strategies;

use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Helpers\Stats;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
/**
 * Mean
 *
 * This strategy always predicts the mean of the fitted data.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class Mean implements Strategy
{
    /**
     * The mean of the fitted values.
     *
     * @var float|null
     */
    protected ?float $mean = null;
    /**
     * Return the data type the strategy handles.
     *
     * @return DataType
     */
    public function type() : DataType
    {
        return DataType::continuous();
    }
    /**
     * Has the strategy been fitted?
     *
     * @internal
     *
     * @return bool
     */
    public function fitted() : bool
    {
        return isset($this->mean);
    }
    /**
     * Fit the guessing strategy to a set of values.
     *
     * @internal
     * @param list<int|float> $values
     * @throws InvalidArgumentException
     */
    public function fit(array $values) : void
    {
        if (empty($values)) {
            throw new InvalidArgumentException('Strategy must be' . ' fitted to at least 1 value.');
        }
        $this->mean = Stats::mean($values);
    }
    /**
     * Make a continuous guess.
     *
     * @internal
     *
     * @throws RuntimeException
     * @return float
     */
    public function guess() : float
    {
        if ($this->mean === null) {
            throw new RuntimeException('Strategy has not been fitted.');
        }
        return $this->mean;
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
        return 'Mean';
    }
}
