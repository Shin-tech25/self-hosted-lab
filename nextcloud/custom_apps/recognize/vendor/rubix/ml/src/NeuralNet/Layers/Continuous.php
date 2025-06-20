<?php

namespace OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\Deferred;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\Optimizer;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\CostFunctions\LeastSquares;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\CostFunctions\RegressionLoss;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
/**
 * Continuous
 *
 * The Continuous output layer consists of a single linear neuron that outputs a scalar value.
 *
 * @internal
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
class Continuous implements Output
{
    /**
     * The function that computes the loss of erroneous activations.
     *
     * @var RegressionLoss
     */
    protected RegressionLoss $costFn;
    /**
     * The memorized input matrix.
     *
     * @var Matrix|null
     */
    protected ?Matrix $input = null;
    /**
     * @param RegressionLoss|null $costFn
     */
    public function __construct(?RegressionLoss $costFn = null)
    {
        $this->costFn = $costFn ?? new LeastSquares();
    }
    /**
     * Return the width of the layer.
     *
     * @return positive-int
     */
    public function width() : int
    {
        return 1;
    }
    /**
     * Initialize the layer with the fan in from the previous layer and return
     * the fan out for this layer.
     *
     * @param positive-int $fanIn
     * @throws InvalidArgumentException
     * @return positive-int
     */
    public function initialize(int $fanIn) : int
    {
        if ($fanIn !== 1) {
            throw new InvalidArgumentException('Fan in must be' . " equal to 1, {$fanIn} given.");
        }
        return 1;
    }
    /**
     * Compute a forward pass through the layer.
     *
     * @param Matrix $input
     * @return Matrix
     */
    public function forward(Matrix $input) : Matrix
    {
        $this->input = $input;
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
        return $input;
    }
    /**
     * Compute the gradient and loss at the output.
     *
     * @param (int|float)[] $labels
     * @param Optimizer $optimizer
     * @throws RuntimeException
     * @return (\Rubix\ML\Deferred|float)[]
     */
    public function back(array $labels, Optimizer $optimizer) : array
    {
        if (!$this->input) {
            throw new RuntimeException('Must perform forward pass' . ' before backpropagating.');
        }
        $expected = Matrix::quick([$labels]);
        $input = $this->input;
        $gradient = new Deferred([$this, 'gradient'], [$input, $expected]);
        $loss = $this->costFn->compute($input, $expected);
        $this->input = null;
        return [$gradient, $loss];
    }
    /**
     * Calculate the gradient for the previous layer.
     *
     * @param Matrix $input
     * @param Matrix $expected
     * @return Matrix
     */
    public function gradient(Matrix $input, Matrix $expected) : Matrix
    {
        return $this->costFn->differentiate($input, $expected)->divide($input->n());
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
        return "Continuous (cost function: {$this->costFn})";
    }
}
