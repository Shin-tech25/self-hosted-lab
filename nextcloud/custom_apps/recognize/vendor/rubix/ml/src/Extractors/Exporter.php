<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Extractors;

/**
 * Exporter
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
interface Exporter
{
    /**
     * Export an iterable data table.
     *
     * @param iterable<mixed[]> $iterator
     */
    public function export(iterable $iterator) : void;
}
