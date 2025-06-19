<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Exceptions;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
/** @internal */
class IncorrectDatasetDimensionality extends InvalidArgumentException
{
    /**
     * @param Dataset $dataset
     * @param int $dimensions
     */
    public function __construct(Dataset $dataset, int $dimensions)
    {
        $message = 'Dataset must contain samples with' . " exactly {$dimensions} dimensions," . " {$dataset->numFeatures()} given.";
        parent::__construct($message);
    }
}
