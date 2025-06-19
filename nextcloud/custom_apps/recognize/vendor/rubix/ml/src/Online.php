<?php

namespace OCA\Recognize\Vendor\Rubix\ML;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
/**
 * Online
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
interface Online extends Learner
{
    /**
     * Perform a partial train on the learner.
     *
     * @param Dataset $dataset
     */
    public function partial(Dataset $dataset) : void;
}
