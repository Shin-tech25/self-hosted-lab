<?php

namespace OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Initializers;

use OCA\Recognize\Vendor\Tensor\Matrix;
use Stringable;
/**
 * Initializer
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
interface Initializer extends Stringable
{
    /**
     * Initialize a weight matrix W in the dimensions fan in x fan out.
     *
     * @internal
     *
     * @param int<0,max> $fanIn
     * @param int<0,max> $fanOut
     * @return Matrix
     */
    public function initialize(int $fanIn, int $fanOut) : Matrix;
}
