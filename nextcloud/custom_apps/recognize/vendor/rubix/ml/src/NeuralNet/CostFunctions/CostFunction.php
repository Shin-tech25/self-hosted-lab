<?php

namespace OCA\Recognize\Vendor\Rubix\ML\NeuralNet\CostFunctions;

use OCA\Recognize\Vendor\Tensor\Matrix;
use Stringable;
/**
 * Cost Function
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
interface CostFunction extends Stringable
{
    /**
     * Compute the loss score.
     *
     * @internal
     *
     * @param Matrix $output
     * @param Matrix $target
     * @return float
     */
    public function compute(Matrix $output, Matrix $target) : float;
    /**
     * Calculate the gradient of the cost function with respect to the output.
     *
     * @internal
     *
     * @param Matrix $output
     * @param Matrix $target
     * @return Matrix
     */
    public function differentiate(Matrix $output, Matrix $target) : Matrix;
}
