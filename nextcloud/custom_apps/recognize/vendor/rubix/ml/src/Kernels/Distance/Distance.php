<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance;

use Stringable;
/**
 * Distance
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
interface Distance extends Stringable
{
    /**
     * Return the data types that this kernel is compatible with.
     *
     * @internal
     *
     * @return list<\OCA\Recognize\Vendor\Rubix\ML\DataType>
     */
    public function compatibility() : array;
    /**
     * Compute the distance between two vectors.
     *
     * @internal
     *
     * @param list<string|int|float> $a
     * @param list<string|int|float> $b
     * @return float
     */
    public function compute(array $a, array $b) : float;
}
