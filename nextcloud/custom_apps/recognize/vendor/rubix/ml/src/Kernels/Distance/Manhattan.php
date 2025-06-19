<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance;

use OCA\Recognize\Vendor\Rubix\ML\DataType;
/**
 * Manhattan
 *
 * A distance metric that constrains movement to horizontal and vertical, similar to navigating the
 * city blocks of Manhattan. An example of a board game that uses this type of movement is Checkers.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class Manhattan implements Distance
{
    /**
     * Return the data types that this kernel is compatible with.
     *
     * @internal
     *
     * @return list<\OCA\Recognize\Vendor\Rubix\ML\DataType>
     */
    public function compatibility() : array
    {
        return [DataType::continuous()];
    }
    /**
     * Compute the distance between two vectors.
     *
     * @internal
     *
     * @param list<int|float> $a
     * @param list<int|float> $b
     * @return float
     */
    public function compute(array $a, array $b) : float
    {
        $distance = 0.0;
        foreach ($a as $i => $value) {
            $distance += \abs($value - $b[$i]);
        }
        return $distance;
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
        return 'Manhattan';
    }
}
