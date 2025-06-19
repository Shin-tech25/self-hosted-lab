<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
/**
 * Elastic
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
interface Elastic extends Stateful
{
    /**
     * Update the fitting of the transformer.
     *
     * @param Dataset $dataset
     */
    public function update(Dataset $dataset) : void;
}
