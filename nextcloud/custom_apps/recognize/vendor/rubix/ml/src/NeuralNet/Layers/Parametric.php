<?php

namespace OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers;

use Generator;
/**
 * Parametric
 *
 * @internal
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
interface Parametric
{
    /**
     * Return the parameters of the layer.
     *
     * @return \Generator<\OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Parameter>
     */
    public function parameters() : Generator;
    /**
     * Restore the parameters on the layer from an associative array.
     *
     * @param \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Parameter[] $parameters
     */
    public function restore(array $parameters) : void;
}
