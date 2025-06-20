<?php

namespace OCA\Recognize\Vendor\Rubix\ML;

/**
 * Ranks Features
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
interface RanksFeatures extends Trainable
{
    /**
     * Return the importance scores of each feature column of the training set.
     *
     * @return float[]
     */
    public function featureImportances() : array;
}
