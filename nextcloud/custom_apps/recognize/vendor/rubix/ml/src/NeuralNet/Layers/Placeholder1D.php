<?php

namespace OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
/**
 * Placeholder 1D
 *
 * The Placeholder 1D input layer represents the *future* input values of a mini
 * batch (matrix) of single dimensional tensors (vectors) to the neural network.
 *
 * @internal
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
class Placeholder1D implements Input
{
    /**
     * The number of input nodes. i.e. feature inputs.
     *
     * @var positive-int
     */
    protected int $inputs;
    /**
     * @param int $inputs
     * @throws InvalidArgumentException
     */
    public function __construct(int $inputs)
    {
        if ($inputs < 1) {
            throw new InvalidArgumentException('Number of input nodes' . " must be greater than 0, {$inputs} given.");
        }
        $this->inputs = $inputs;
    }
    /**
     * @return positive-int
     */
    public function width() : int
    {
        return $this->inputs;
    }
    /**
     * Initialize the layer with the fan in from the previous layer and return
     * the fan out for this layer.
     *
     * @param positive-int $fanIn
     * @return positive-int
     */
    public function initialize(int $fanIn) : int
    {
        return $this->inputs;
    }
    /**
     * Compute a forward pass through the layer.
     *
     * @param Matrix $input
     * @throws InvalidArgumentException
     * @return Matrix
     */
    public function forward(Matrix $input) : Matrix
    {
        if ($input->m() !== $this->inputs) {
            throw new InvalidArgumentException('The number of features' . ' and input nodes must be equal,' . " {$this->inputs} expected but {$input->m()} given.");
        }
        return $input;
    }
    /**
     * Compute an inferential pass through the layer.
     *
     * @param Matrix $input
     * @return Matrix
     */
    public function infer(Matrix $input) : Matrix
    {
        return $this->forward($input);
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
        return "Placeholder 1D (inputs: {$this->inputs})";
    }
}
