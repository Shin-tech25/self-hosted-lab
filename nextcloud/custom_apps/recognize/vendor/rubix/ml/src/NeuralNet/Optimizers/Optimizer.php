<?php

namespace OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers;

use OCA\Recognize\Vendor\Tensor\Tensor;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Parameter;
use Stringable;
/**
 * Optimizer
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
interface Optimizer extends Stringable
{
    /**
     * Take a step of gradient descent for a given parameter.
     *
     * @internal
     *
     * @param Parameter $param
     * @param \Tensor\Tensor<int|float|array> $gradient
     * @return \Tensor\Tensor<int|float|array>
     */
    public function step(Parameter $param, Tensor $gradient) : Tensor;
}
