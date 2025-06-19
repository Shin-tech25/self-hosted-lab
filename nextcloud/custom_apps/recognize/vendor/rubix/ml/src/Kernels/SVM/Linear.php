<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Kernels\SVM;

use OCA\Recognize\Vendor\Rubix\ML\Specifications\ExtensionIsLoaded;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SpecificationChain;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\ExtensionMinimumVersion;
use svm;
/**
 * Linear
 *
 * A simple linear kernel computed by the dot product.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class Linear implements Kernel
{
    public function __construct()
    {
        SpecificationChain::with([new ExtensionIsLoaded('svm'), new ExtensionMinimumVersion('svm', '0.2.0')])->check();
    }
    /**
     * Return the options for the libsvm runtime.
     *
     * @internal
     *
     * @return mixed[]
     */
    public function options() : array
    {
        return [svm::OPT_KERNEL_TYPE => svm::KERNEL_LINEAR];
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
        return 'Linear';
    }
}
