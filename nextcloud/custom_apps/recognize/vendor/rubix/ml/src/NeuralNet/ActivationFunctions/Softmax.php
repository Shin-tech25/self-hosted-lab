<?php

namespace OCA\Recognize\Vendor\Rubix\ML\NeuralNet\ActivationFunctions;

use OCA\Recognize\Vendor\Tensor\Matrix;
use const OCA\Recognize\Vendor\Rubix\ML\EPSILON;
/**
 * Softmax
 *
 * The Softmax function is a generalization of the Sigmoid function that squashes
 * each activation between 0 and 1, and all activations add up to 1.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class Softmax extends Sigmoid
{
    /**
     * Compute the activation.
     *
     * @internal
     *
     * @param Matrix $input
     * @return Matrix
     */
    public function activate(Matrix $input) : Matrix
    {
        $zHat = $input->exp()->transpose();
        $total = $zHat->sum()->clipLower(EPSILON);
        return $zHat->divide($total)->transpose();
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
        return 'Softmax';
    }
}
