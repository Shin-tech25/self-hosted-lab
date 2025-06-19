<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Regressors;

use OCA\Recognize\Vendor\Rubix\ML\Online;
use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\Helpers\Stats;
use OCA\Recognize\Vendor\Rubix\ML\Helpers\Params;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Traits\AutotrackRevisions;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\Distance;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\Euclidean;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsLabeled;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsNotEmpty;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SpecificationChain;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetHasDimensionality;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\LabelsAreCompatibleWithLearner;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SamplesAreCompatibleWithEstimator;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use function array_slice;
/**
 * KNN Regressor
 *
 * A version of the K Nearest Neighbors algorithm that uses the average (mean) outcome of
 * the *k* nearest data points to an unknown sample in order to make continuous-valued
 * predictions suitable for regression problems.
 *
 * > **Note:** This learner is considered a *lazy* learner because it does the majority
 * of its computation during inference. For a fast spatial tree-accelerated version, see
 * KD Neighbors Regressor.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class KNNRegressor implements Estimator, Learner, Online, Persistable
{
    use AutotrackRevisions;
    /**
     * The number of neighbors to consider when making a prediction.
     *
     * @var int
     */
    protected int $k;
    /**
     * Should we consider the distances of our nearest neighbors when making predictions?
     *
     * @var bool
     */
    protected bool $weighted;
    /**
     * The distance kernel to use when computing the distances.
     *
     * @var Distance
     */
    protected Distance $kernel;
    /**
     * The training samples.
     *
     * @var list<(string|int|float)[]>
     */
    protected array $samples = [];
    /**
     * The training labels.
     *
     * @var list<int|float>
     */
    protected array $labels = [];
    /**
     * @param int $k
     * @param bool $weighted
     * @param Distance|null $kernel
     * @throws InvalidArgumentException
     */
    public function __construct(int $k = 5, bool $weighted = \false, ?Distance $kernel = null)
    {
        if ($k < 1) {
            throw new InvalidArgumentException('At least 1 neighbor is required' . " to make a prediction, {$k} given.");
        }
        $this->k = $k;
        $this->weighted = $weighted;
        $this->kernel = $kernel ?? new Euclidean();
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
        return EstimatorType::regressor();
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
        return $this->kernel->compatibility();
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
        return ['k' => $this->k, 'weighted' => $this->weighted, 'kernel' => $this->kernel];
    }
    /**
     * Has the learner been trained?
     *
     * @return bool
     */
    public function trained() : bool
    {
        return $this->samples and $this->labels;
    }
    /**
     * Train the learner with a dataset.
     *
     * @param \OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled $dataset
     */
    public function train(Dataset $dataset) : void
    {
        $this->samples = $this->labels = [];
        $this->partial($dataset);
    }
    /**
     * Perform a partial train on the learner.
     *
     * @param \OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled $dataset
     */
    public function partial(Dataset $dataset) : void
    {
        SpecificationChain::with([new DatasetIsLabeled($dataset), new DatasetIsNotEmpty($dataset), new SamplesAreCompatibleWithEstimator($dataset, $this), new LabelsAreCompatibleWithLearner($dataset, $this)])->check();
        $this->samples = \array_merge($this->samples, $dataset->samples());
        $this->labels = \array_merge($this->labels, $dataset->labels());
    }
    /**
     * Make a prediction based on the nearest neighbors.
     *
     * @param Dataset $dataset
     * @throws RuntimeException
     * @return list<int|float>
     */
    public function predict(Dataset $dataset) : array
    {
        if (!$this->samples or !$this->labels) {
            throw new RuntimeException('Estimator has not been trained.');
        }
        DatasetHasDimensionality::with($dataset, \count(\current($this->samples)))->check();
        return \array_map([$this, 'predictSample'], $dataset->samples());
    }
    /**
     * Predict a single sample and return the result.
     *
     * @internal
     *
     * @param list<string|int|float> $sample
     * @return int|float
     */
    public function predictSample(array $sample)
    {
        [$labels, $distances] = $this->nearest($sample);
        if ($this->weighted) {
            $weights = [];
            foreach ($distances as $distance) {
                $weights[] = 1.0 / (1.0 + $distance);
            }
            return Stats::weightedMean(\array_values($labels), $weights);
        }
        return Stats::mean($labels);
    }
    /**
     * Find the K nearest neighbors to the given sample vector using the brute force method.
     *
     * @param (string|int|float)[] $sample
     * @return array{list<string|int|float>,list<float>}
     */
    protected function nearest(array $sample) : array
    {
        $distances = [];
        foreach ($this->samples as $neighbor) {
            $distances[] = $this->kernel->compute($sample, $neighbor);
        }
        \asort($distances);
        $distances = array_slice($distances, 0, $this->k, \true);
        $labels = \array_intersect_key($this->labels, $distances);
        return [$labels, $distances];
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
        return 'KNN Regressor (' . Params::stringify($this->params()) . ')';
    }
}
