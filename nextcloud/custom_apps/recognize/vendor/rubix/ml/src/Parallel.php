<?php

namespace OCA\Recognize\Vendor\Rubix\ML;

use OCA\Recognize\Vendor\Rubix\ML\Backends\Backend;
/**
 * Parallel
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
interface Parallel
{
    /**
     * Set the parallel processing backend.
     *
     * @param Backend $backend
     */
    public function setBackend(Backend $backend) : void;
}
