<?php

namespace OCA\Recognize\Vendor\Rubix\ML;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
/** @internal */
interface Trainable
{
    /**
     * Train the learner with a dataset.
     *
     * @param Dataset $dataset
     */
    public function train(Dataset $dataset) : void;
    /**
     * Has the learner been trained?
     *
     * @return bool
     */
    public function trained() : bool;
}
