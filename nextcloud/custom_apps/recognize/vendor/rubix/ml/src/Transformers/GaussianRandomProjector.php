<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Transformers;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Traits\AutotrackRevisions;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SamplesAreCompatibleWithTransformer;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
/**
 * Gaussian Random Projector
 *
 * A Random Projector is a dimensionality reducer based on the
 * Johnson-Lindenstrauss lemma that uses a random matrix to project a feature
 * vector onto a user-specified number of dimensions. It is faster than most
 * non-randomized dimensionality reduction techniques and offers similar
 * performance. This version uses a random matrix sampled from a Gaussian
 * distribution.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class GaussianRandomProjector implements Transformer, Stateful, Persistable
{
    use AutotrackRevisions;
    /**
     * The target number of dimensions.
     *
     * @var positive-int
     */
    protected int $dimensions;
    /**
     * The random matrix.
     *
     * @var Matrix|null
     */
    protected ?Matrix $r = null;
    /**
     * Estimate the minimum dimensionality needed to satisfy a *max distortion* constraint with *n*
     * samples using the Johnson-Lindenstrauss lemma.
     *
     * @param int $n
     * @param float $maxDistortion
     * @throws InvalidArgumentException
     * @return int
     */
    public static function minDimensions(int $n, float $maxDistortion = 0.5) : int
    {
        if ($n < 0) {
            throw new InvalidArgumentException('Number of samples' . " must be be greater than 0, {$n} given.");
        }
        if ($maxDistortion <= 0.0) {
            throw new InvalidArgumentException('Max distortion must be' . " greater than 0, {$maxDistortion} given.");
        }
        $denominator = $maxDistortion ** 2 / 2.0 - $maxDistortion ** 3 / 3.0;
        return (int) \round(4.0 * \log($n) / $denominator);
    }
    /**
     * @param int $dimensions
     * @throws InvalidArgumentException
     */
    public function __construct(int $dimensions)
    {
        if ($dimensions < 1) {
            throw new InvalidArgumentException('Dimensions must be' . " greater than 0, {$dimensions} given.");
        }
        $this->dimensions = $dimensions;
    }
    /**
     * Return the data types that this transformer is compatible with.
     *
     * @internal
     *
     * @return list<\OCA\Recognize\Vendor\Rubix\ML\DataType>
     */
    public function compatibility() : array
    {
        return [DataType::continuous()];
    }
    /**
     * Is the transformer fitted?
     *
     * @return bool
     */
    public function fitted() : bool
    {
        return isset($this->r);
    }
    /**
     * Fit the transformer to a dataset.
     *
     * @param Dataset $dataset
     * @throws InvalidArgumentException
     */
    public function fit(Dataset $dataset) : void
    {
        SamplesAreCompatibleWithTransformer::with($dataset, $this)->check();
        $this->r = Matrix::gaussian($dataset->numFeatures(), $this->dimensions);
    }
    /**
     * Transform the dataset in place.
     *
     * @param list<list<mixed>> $samples
     * @throws RuntimeException
     */
    public function transform(array &$samples) : void
    {
        if (!$this->r) {
            throw new RuntimeException('Transformer has not been fitted.');
        }
        $samples = Matrix::quick($samples)->matmul($this->r)->asArray();
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
        return "Gaussian Random Projector (dimensions: {$this->dimensions})";
    }
}
