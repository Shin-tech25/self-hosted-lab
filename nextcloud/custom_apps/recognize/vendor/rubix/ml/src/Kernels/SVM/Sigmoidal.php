<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Kernels\SVM;

use OCA\Recognize\Vendor\Rubix\ML\Specifications\ExtensionIsLoaded;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SpecificationChain;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\ExtensionMinimumVersion;
use svm;
/**
 * Sigmoidal
 *
 * S shaped nonlinearity kernel.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class Sigmoidal implements Kernel
{
    /**
     * The kernel coefficient.
     *
     * @var float|null
     */
    protected ?float $gamma;
    /**
     * The independent term.
     *
     * @var float
     */
    protected float $coef0;
    /**
     * @param float $gamma
     * @param float $coef0
     */
    public function __construct(?float $gamma = null, float $coef0 = 0.0)
    {
        SpecificationChain::with([new ExtensionIsLoaded('svm'), new ExtensionMinimumVersion('svm', '0.2.0')])->check();
        $this->gamma = $gamma;
        $this->coef0 = $coef0;
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
        return [svm::OPT_KERNEL_TYPE => svm::KERNEL_SIGMOID, svm::OPT_GAMMA => $this->gamma, svm::OPT_COEF_ZERO => $this->coef0];
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
        return "Sigmoidal (gamma: {$this->gamma}, coef0: {$this->coef0})";
    }
}
