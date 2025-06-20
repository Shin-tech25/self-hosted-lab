<?php

namespace OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers;

use OCA\Recognize\Vendor\Tensor\Tensor;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Parameter;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use function get_class;
use const OCA\Recognize\Vendor\Rubix\ML\EPSILON;
/**
 * RMS Prop
 *
 * An adaptive gradient technique that divides the current gradient over a rolling window
 * of magnitudes of recent gradients.
 *
 * References:
 * [1] T. Tieleman et al. (2012). Lecture 6e rmsprop: Divide the
 * gradient by a running average of its recent magnitude.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class RMSProp implements Optimizer, Adaptive
{
    /**
     * The learning rate that controls the global step size.
     *
     * @var float
     */
    protected float $rate;
    /**
     * The rms decay rate.
     *
     * @var float
     */
    protected float $decay;
    /**
     * The opposite of the rms decay rate.
     *
     * @var float
     */
    protected float $rho;
    /**
     * The cache of running squared gradients.
     *
     * @var \Tensor\Tensor[]
     */
    protected array $cache = [];
    /**
     * @param float $rate
     * @param float $decay
     * @throws InvalidArgumentException
     */
    public function __construct(float $rate = 0.001, float $decay = 0.1)
    {
        if ($rate <= 0.0) {
            throw new InvalidArgumentException('Learning rate must be' . " greater than 0, {$rate} given.");
        }
        if ($decay <= 0.0 or $decay >= 1.0) {
            throw new InvalidArgumentException('Decay must be between' . " 0 and 1, {$decay} given.");
        }
        $this->rate = $rate;
        $this->decay = $decay;
        $this->rho = 1.0 - $decay;
    }
    /**
     * Warm the parameter cache.
     *
     * @internal
     *
     * @param Parameter $param
     * @throws RuntimeException
     */
    public function warm(Parameter $param) : void
    {
        $class = get_class($param->param());
        if ($class === \false) {
            throw new RuntimeException('Could not locate parameter class.');
        }
        $this->cache[$param->id()] = $class::zeros(...$param->param()->shape());
    }
    /**
     * Take a step of gradient descent for a given parameter.
     *
     * @internal
     *
     * @param Parameter $param
     * @param \Tensor\Tensor<int|float|array> $gradient
     * @return \Tensor\Tensor<int|float|array>
     */
    public function step(Parameter $param, Tensor $gradient) : Tensor
    {
        $norm = $this->cache[$param->id()];
        $norm = $norm->multiply($this->rho)->add($gradient->square()->multiply($this->decay));
        $this->cache[$param->id()] = $norm;
        return $gradient->multiply($this->rate)->divide($norm->sqrt()->clipLower(EPSILON));
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
        return "RMS Prop (rate: {$this->rate}, decay: {$this->decay})";
    }
}
