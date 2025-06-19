<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Clusterers\Seeders;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
/**
 * Random
 *
 * Completely random selection of seeds from a given dataset.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class Random implements Seeder
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
    public function seed(Dataset $dataset, int $k) : array
    {
        return $dataset->randomSubset($k)->samples();
    }
    /**
     * Return the string representation of the object.
     *
     * @internal
     *
     * @return string
     */
    public function __toString() : string
    {
        return 'Random';
    }
}
