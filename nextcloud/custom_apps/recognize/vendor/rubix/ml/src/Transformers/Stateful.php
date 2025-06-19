<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
/**
 * Stateful
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
interface Stateful extends Transformer
{
    /**
     * Fit the transformer to a dataset.
     *
     * @param Dataset $dataset
     */
    public function fit(Dataset $dataset) : void;
    /**
     * Is the transformer fitted?
     *
     * @return bool
     */
    public function fitted() : bool;
}
