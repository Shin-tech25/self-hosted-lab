<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Transformers;

/**
 * Reversible
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Alex Torchenko
 * @internal
 */
interface Reversible extends Transformer
{
    /**
     * Perform the reverse transformation to the samples.
     *
     * @param list<list<mixed>> $samples
     */
    public function reverseTransform(array &$samples) : void;
}
