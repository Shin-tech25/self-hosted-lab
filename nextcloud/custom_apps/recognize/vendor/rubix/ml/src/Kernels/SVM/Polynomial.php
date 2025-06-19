<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Kernels\SVM;

use OCA\Recognize\Vendor\Rubix\ML\Specifications\ExtensionIsLoaded;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SpecificationChain;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\ExtensionMinimumVersion;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use svm;
/**
 * Polynomial
 *
 * Operating in high dimensions, the polynomial to the pth degree of the sample vector.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class Polynomial implements Kernel
{
    /**
     * The degree of the polynomial.
     *
     * @var int
     */
    protected int $degree;
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
     * @param int $degree
     * @param float $gamma
     * @param float $coef0
     * @throws InvalidArgumentException
     */
    public function __construct(int $degree = 3, ?float $gamma = null, float $coef0 = 0.0)
    {
        SpecificationChain::with([new ExtensionIsLoaded('svm'), new ExtensionMinimumVersion('svm', '0.2.0')])->check();
        if ($degree < 1) {
            throw new InvalidArgumentException('Degree must be greater than 0,' . " {$degree} given.");
        }
        $this->degree = $degree;
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
        return [svm::OPT_KERNEL_TYPE => svm::KERNEL_POLY, svm::OPT_DEGREE => $this->degree, svm::OPT_GAMMA => $this->gamma, svm::OPT_COEF_ZERO => $this->coef0];
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
        return "Polynomial (degree: {$this->degree}, gamma: {$this->gamma}, coef0: {$this->coef0})";
    }
}
