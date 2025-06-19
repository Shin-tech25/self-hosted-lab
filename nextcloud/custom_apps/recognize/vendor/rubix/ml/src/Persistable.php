<?php

namespace OCA\Recognize\Vendor\Rubix\ML;

/**
 * Persistable
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
interface Persistable
{
    /**
     * Return the revision number of the class.
     *
     * @return string
     */
    public function revision() : string;
}
