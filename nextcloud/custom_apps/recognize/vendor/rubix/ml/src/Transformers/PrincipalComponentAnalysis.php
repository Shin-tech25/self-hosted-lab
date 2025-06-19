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
use function array_multisort;
use function array_sum;
use const OCA\Recognize\Vendor\Rubix\ML\EPSILON;
/**
 * Principal Component Analysis
 *
 * Principal Component Analysis or *PCA* is a dimensionality reduction technique that
 * aims to transform the feature space by the *k* principal components that explain
 * the most variance of the data where *k* is the dimensionality of the output
 * specified by the user. PCA is used to compress high dimensional samples down to
 * lower dimensions such that would retain as much of the information within the data
 * as possible.
 *
 * References:
 * [1] H. Abdi et al. (2010). Principal Component Analysis.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class PrincipalComponentAnalysis implements Transformer, Stateful, Persistable
{
    use AutotrackRevisions;
    /**
     * The target number of dimensions to project onto.
     *
     * @var int
     */
    protected int $dimensions;
    /**
     * The matrix of eigenvectors computed at fitting.
     *
     * @var Matrix|null
     */
    protected ?Matrix $eigenvectors = null;
    /**
     * The percentage of information lost due to the transformation.
     *
     * @var float|null
     */
    protected ?float $lossiness = null;
    /**
     * The centers (means) of the input feature columns.
     *
     * @var \Tensor\Vector|null
     */
    protected ?\OCA\Recognize\Vendor\Tensor\Vector $mean = null;
    /**
     * @param int $dimensions
     * @throws InvalidArgumentException
     */
    public function __construct(int $dimensions)
    {
        SpecificationChain::with([new ExtensionIsLoaded('tensor'), new ExtensionMinimumVersion('tensor', '2.1.4')])->check();
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
        return $this->mean and $this->eigenvectors;
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
        $xT = Matrix::quick($dataset->samples())->transpose();
        $eig = $xT->covariance()->eig(\true);
        $eigenvalues = $eig->eigenvalues();
        $eigenvectors = $eig->eigenvectors()->asArray();
        $totalVariance = array_sum($eigenvalues);
        array_multisort($eigenvalues, \SORT_DESC, $eigenvectors);
        $eigenvalues = array_slice($eigenvalues, 0, $this->dimensions);
        $eigenvectors = array_slice($eigenvectors, 0, $this->dimensions);
        $eigenvectors = Matrix::quick($eigenvectors)->transpose();
        $noiseVariance = $totalVariance - array_sum($eigenvalues);
        $lossiness = $noiseVariance / ($totalVariance ?: EPSILON);
        $this->mean = $xT->mean()->transpose();
        $this->eigenvectors = $eigenvectors;
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
        if (!$this->mean or !$this->eigenvectors) {
            throw new RuntimeException('Transformer has not been fitted.');
        }
        $samples = Matrix::build($samples)->subtract($this->mean)->matmul($this->eigenvectors)->asArray();
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
        return "Principal Component Analysis (dimensions: {$this->dimensions})";
    }
}
