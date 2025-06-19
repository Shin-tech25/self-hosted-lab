<?php

namespace OCA\Recognize\Vendor\Rubix\ML\AnomalyDetectors;

use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
/** @internal */
interface Scoring extends Estimator
{
    /**
     * Return the anomaly scores assigned to the samples in a dataset.
     *
     * @param Dataset $dataset
     * @return float[]
     */
    public function score(Dataset $dataset) : array;
}
