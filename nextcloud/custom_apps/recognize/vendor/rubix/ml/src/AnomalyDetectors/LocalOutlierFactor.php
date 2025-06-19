<?php

namespace OCA\Recognize\Vendor\Rubix\ML\AnomalyDetectors;

use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\Helpers\Stats;
use OCA\Recognize\Vendor\Rubix\ML\Helpers\Params;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
use OCA\Recognize\Vendor\Rubix\ML\Graph\Trees\KDTree;
use OCA\Recognize\Vendor\Rubix\ML\Graph\Trees\Spatial;
use OCA\Recognize\Vendor\Rubix\ML\Traits\AutotrackRevisions;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsNotEmpty;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SpecificationChain;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetHasDimensionality;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SamplesAreCompatibleWithEstimator;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use const OCA\Recognize\Vendor\Rubix\ML\EPSILON;
/**
 * Local Outlier Factor
 *
 * Local Outlier Factor (LOF) measures the local deviation of density of an unknown
 * sample with respect to its *k* nearest neighbors from the training set. As such,
 * LOF only considers the local region (or *neighborhood*) of an unknown sample
 * which enables it to detect anomalies within individual clusters of data.
 *
 * References:
 * [1] M. M. Breunig et al. (2000). LOF: Identifying Density-Based Local Outliers.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class LocalOutlierFactor implements Estimator, Learner, Scoring, Persistable
{
    use AutotrackRevisions;
    /**
     * The default minimum anomaly score for a sample to be flagged.
     *
     * @var float
     */
    protected const DEFAULT_THRESHOLD = 1.5;
    /**
     * The number of nearest neighbors to consider a local region.
     *
     * @var int
     */
    protected int $k;
    /**
     * The percentage of outliers that are assumed to be present in the training set.
     *
     * @var float|null
     */
    protected ?float $contamination = null;
    /**
     * The k-d tree used for nearest neighbor queries.
     *
     * @var Spatial
     */
    protected Spatial $tree;
    /**
     * The precomputed k distances between each training sample and its k'th nearest neighbor.
     *
     * @var float[]
     */
    protected array $kdistances = [];
    /**
     * The precomputed local reachability densities of the training set.
     *
     * @var float[]
     */
    protected array $lrds = [];
    /**
     * The local outlier factor threshold used by the decision function.
     *
     * @var float|null
     */
    protected ?float $threshold = null;
    /**
     * The dimensionality of the training set.
     *
     * @var int|null
     */
    protected ?int $featureCount = null;
    /**
     * @param int $k
     * @param float|null $contamination
     * @param Spatial|null $tree
     * @throws InvalidArgumentException
     */
    public function __construct(int $k = 20, ?float $contamination = null, ?Spatial $tree = null)
    {
        if ($k < 1) {
            throw new InvalidArgumentException('At least 1 neighbor' . " is required to form a local region, {$k} given.");
        }
        if (isset($contamination) and ($contamination < 0.0 or $contamination > 0.5)) {
            throw new InvalidArgumentException('Contamination must be' . " between 0 and 0.5, {$contamination} given.");
        }
        $this->k = $k;
        $this->contamination = $contamination;
        $this->tree = $tree ?? new KDTree();
    }
    /**
     * Return the estimator type.
     *
     * @internal
     *
     * @return EstimatorType
     */
    public function type() : EstimatorType
    {
        return EstimatorType::anomalyDetector();
    }
    /**
     * Return the data types that the estimator is compatible with.
     *
     * @internal
     *
     * @return list<\OCA\Recognize\Vendor\Rubix\ML\DataType>
     */
    public function compatibility() : array
    {
        return $this->tree->kernel()->compatibility();
    }
    /**
     * Return the settings of the hyper-parameters in an associative array.
     *
     * @internal
     *
     * @return mixed[]
     */
    public function params() : array
    {
        return ['k' => $this->k, 'contamination' => $this->contamination, 'tree' => $this->tree];
    }
    /**
     * Has the learner been trained?
     *
     * @return bool
     */
    public function trained() : bool
    {
        return !$this->tree->bare() and $this->kdistances and $this->lrds;
    }
    /**
     * Return the base spatial tree instance.
     *
     * @return Spatial
     */
    public function tree() : Spatial
    {
        return $this->tree;
    }
    /**
     * Train the learner with a dataset.
     *
     * @param Dataset $dataset
     */
    public function train(Dataset $dataset) : void
    {
        SpecificationChain::with([new DatasetIsNotEmpty($dataset), new SamplesAreCompatibleWithEstimator($dataset, $this)])->check();
        $labels = \range(0, $dataset->numSamples() - 1);
        $dataset = Labeled::quick($dataset->samples(), $labels);
        $this->tree->grow($dataset);
        $this->kdistances = $this->lrds = [];
        $iHat = $dHat = [];
        foreach ($dataset->samples() as $sample) {
            [$samples, $indices, $distances] = $this->tree->nearest($sample, $this->k);
            $iHat[] = $indices;
            $dHat[] = $distances;
            $this->kdistances[] = \end($distances) ?: \INF;
        }
        $this->lrds = \array_map([$this, 'localReachabilityDensity'], $iHat, $dHat);
        if (isset($this->contamination)) {
            $lofs = \array_map([$this, 'localOutlierFactor'], $dataset->samples());
            $threshold = Stats::quantile($lofs, 1.0 - $this->contamination);
        }
        $this->threshold = $threshold ?? self::DEFAULT_THRESHOLD;
        $this->featureCount = $dataset->numFeatures();
    }
    /**
     * Make predictions from a dataset.
     *
     * @param Dataset $dataset
     * @return list<int>
     */
    public function predict(Dataset $dataset) : array
    {
        if ($this->tree->bare() or !$this->featureCount) {
            throw new RuntimeException('Estimator has not been trained.');
        }
        DatasetHasDimensionality::with($dataset, $this->featureCount)->check();
        return \array_map([$this, 'predictSample'], $dataset->samples());
    }
    /**
     * Predict a single sample and return the result.
     *
     * @internal
     *
     * @param list<int|float> $sample
     * @return int
     */
    public function predictSample(array $sample) : int
    {
        return $this->localOutlierFactor($sample) > $this->threshold ? 1 : 0;
    }
    /**
     * Return the anomaly scores assigned to the samples in a dataset.
     *
     * @param Dataset $dataset
     * @throws RuntimeException
     * @return list<float>
     */
    public function score(Dataset $dataset) : array
    {
        if ($this->tree->bare() or !$this->featureCount) {
            throw new RuntimeException('Estimator has not been trained.');
        }
        DatasetHasDimensionality::with($dataset, $this->featureCount)->check();
        return \array_map([$this, 'localOutlierFactor'], $dataset->samples());
    }
    /**
     * Calculate the local outlier factor of a given sample given its k
     * nearest neighbors.
     *
     * @param list<int|float> $sample
     * @return float
     */
    protected function localOutlierFactor(array $sample) : float
    {
        [$samples, $indices, $distances] = $this->tree->nearest($sample, $this->k);
        $lrd = $this->localReachabilityDensity($indices, $distances) ?: EPSILON;
        $ratios = [];
        foreach ($indices as $index) {
            $ratios[] = $this->lrds[$index] / $lrd;
        }
        return Stats::mean($ratios);
    }
    /**
     * Calculate the local reachability density of a sample given its
     * distances to its k nearest neighbors.
     *
     * @param list<string|int|float> $indices
     * @param list<float> $distances
     * @return float
     */
    protected function localReachabilityDensity(array $indices, array $distances) : float
    {
        $kdistances = [];
        foreach ($indices as $index) {
            $kdistances[] = $this->kdistances[$index];
        }
        $rds = \array_map('max', $distances, $kdistances);
        return 1.0 / (Stats::mean($rds) ?: EPSILON);
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
        return 'Local Outlier Factor (' . Params::stringify($this->params()) . ')';
    }
}
