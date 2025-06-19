<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Clusterers\Seeders;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use Stringable;
/**
 * Seeder
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
interface Seeder extends Stringable
{
    /**
     * Seed k cluster centroids from a dataset.
     *
     * @internal
     *
     * @param Dataset $dataset
     * @param int $k
     * @return list<list<string|int|float>>
     */
    public function seed(Dataset $dataset, int $k) : array;
}
