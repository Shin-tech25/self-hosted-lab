<?php

namespace OCA\Recognize\Vendor\Rubix\ML\NeuralNet;

use Traversable;
/**
 * Network
 *
 * @internal
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
interface Network
{
    /**
     * Return the layers of the network.
     *
     * @return \Traversable<\OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Layer>
     */
    public function layers() : Traversable;
}
