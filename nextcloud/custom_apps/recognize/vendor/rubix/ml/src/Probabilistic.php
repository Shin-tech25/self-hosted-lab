<?php

namespace OCA\Recognize\Vendor\Rubix\ML;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
/**
 * Probabilistic
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
interface Probabilistic extends Estimator
{
    /**
     * Estimate the joint probabilities for each possible outcome.
     *
     * @param Dataset $dataset
     * @return list<float[]>
     */
    public function proba(Dataset $dataset) : array;
}
