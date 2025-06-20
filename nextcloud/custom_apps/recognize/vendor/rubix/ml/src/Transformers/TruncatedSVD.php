<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Transformers;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Traits\AutotrackRevisions;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\ExtensionIsLoaded;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SpecificationChain;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\ExtensionMinimumVersion;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SamplesAreCompatibleWithTransformer;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use function array_slice;
use function array_sum;
use const OCA\Recognize\Vendor\Rubix\ML\EPSILON;
/**
 * Truncated SVD
 *
 * Truncated Singular Value Decomposition (SVD) is a matrix factorization and dimensionality reduction technique that generalizes
 * eigendecomposition to general matrices. When applied to datasets of document term frequency vectors, the technique is called
 * Latent Semantic Analysis (LSA) and computes a statistical model of relationships between words.
 *
 * References:
 * [1] S. Deerwater et al. (1990). Indexing by Latent Semantic Analysis.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class TruncatedSVD implements Transformer, Stateful, Persistable
{
    use AutotrackRevisions;
    /**
     * The target number of dimensions to project onto.
     *
     * @var int
     */
    protected int $dimensions;
    /**
     * The transposed right singular vectors of the decomposition.
     *
     * @var Matrix|null
     */
    protected ?Matrix $components = null;
    /**
     * The proportion of information lost due to the transformation.
     *
     * @var float|null
     */
    protected ?float $lossiness = null;
    /**
     * @param int $dimensions
     * @throws InvalidArgumentException
     */
    public function __construct(int $dimensions)
    {
        SpecificationChain::with([new ExtensionIsLoaded('tensor'), new ExtensionMinimumVersion('tensor', '2.2.0')])->check();
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
        return isset($this->components);
    }
    /**
     * Return the percentage of information lost due to the transformation.
     *
     * @return float|null
     */
    public function lossiness() : ?float
    {
        return $this->lossiness;
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
        $svd = Matrix::build($dataset->samples())->svd();
        $singularValues = $svd->singularValues();
        $components = $svd->vT()->asArray();
        $totalStdDev = array_sum($singularValues);
        $singularValues = array_slice($singularValues, 0, $this->dimensions);
        $components = array_slice($components, 0, $this->dimensions);
        $components = Matrix::quick($components)->transpose();
        $noiseStdDev = $totalStdDev - array_sum($singularValues);
        $lossiness = $noiseStdDev / ($totalStdDev ?: EPSILON);
        $this->components = $components;
        $this->lossiness = $lossiness;
    }
    /**
     * Transform the dataset in place.
     *
     * @param list<list<mixed>> $samples
     * @throws RuntimeException
     */
    public function transform(array &$samples) : void
    {
        if (!$this->components) {
            throw new RuntimeException('Transformer has not been fitted.');
        }
        $samples = Matrix::build($samples)->matmul($this->components)->asArray();
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
        return "Truncated SVD (dimensions: {$this->dimensions})";
    }
}
