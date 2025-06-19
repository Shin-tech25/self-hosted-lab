<?php

namespace OCA\Recognize\Vendor\Rubix\ML;

/**
 * Wrapper
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Ronan Giron
 * @internal
 */
interface EstimatorWrapper extends Estimator
{
    /**
     * Return the base estimator instance.
     *
     * @return Estimator
     */
    public function base() : Estimator;
}
