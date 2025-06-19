<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Kernels\SVM;

use Stringable;
/**
 * Kernel
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
interface Kernel extends Stringable
{
    /**
     * Return the options for the libsvm runtime.
     *
     * @internal
     *
     * @return mixed[]
     */
    public function options() : array;
}
