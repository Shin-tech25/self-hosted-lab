<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Regressors;

use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\Helpers\Stats;
use OCA\Recognize\Vendor\Rubix\ML\Helpers\Params;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
use OCA\Recognize\Vendor\Rubix\ML\Graph\Trees\Spatial;
use OCA\Recognize\Vendor\Rubix\ML\Graph\Trees\BallTree;
use OCA\Recognize\Vendor\Rubix\ML\Traits\AutotrackRevisions;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsLabeled;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsNotEmpty;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SpecificationChain;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetHasDimensionality;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\LabelsAreCompatibleWithLearner;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SamplesAreCompatibleWithEstimator;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
/**
 * Radius Neighbors Regressor
 *
 * This is the regressor version of Radius Neighbors implementing a binary spatial tree under
 * the hood for fast radius queries. The prediction is a weighted average of each label from
 * the training set that is within a fixed user-defined radius.
 *
 * > **Note**: Unknown samples with no training samples within radius are labeled
 * *NaN*. As such, Radius Neighbors is also a quasi anomaly detector.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class RadiusNeighborsRegressor implements Estimator, Learner, Persistable
{
    use AutotrackRevisions;
    /**
     * The value to assign to outliers when making a prediction.
     *
     * @var mixed
     */
    public const OUTLIER_VALUE = \NAN;
    /**
     * The radius within which points are considered neighbors.
     *
     * @var float
     */
    protected float $radius;
    /**
     * Should we consider the distances of our nearest neighbors when making predictions?
     *
     * @var bool
     */
    protected bool $weighted;
    /**
     * The spatial tree used to run range searches.
     *
     * @var Spatial
     */
    protected Spatial $tree;
    /**
     * The dimensionality of the training set.
     *
     * @var int|null
     */
    protected ?int $featureCount = null;
    /**
     * @param float $radius
     * @param bool $weighted
     * @param Spatial|null $tree
     * @throws InvalidArgumentException
     */
    public function __construct(float $radius = 1.0, bool $weighted = \false, ?Spatial $tree = null)
    {
        if ($radius <= 0.0) {
            throw new InvalidArgumentException('Radius must be' . " greater than 0, {$radius} given.");
        }
        $this->radius = $radius;
        $this->weighted = $weighted;
        $this->tree = $tree ?? new BallTree();
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
        return ['radius' => $this->radius, 'weighted' => $this->weighted, 'tree' => $this->tree];
    }
    /**
     * Has the learner been trained?
     *
     * @return bool
     */
    public function trained() : bool
    {
        return !$this->tree->bare();
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
     * @param Labeled $dataset
     */
    public function train(Dataset $dataset) : void
    {
        SpecificationChain::with([new DatasetIsLabeled($dataset), new DatasetIsNotEmpty($dataset), new SamplesAreCompatibleWithEstimator($dataset, $this), new LabelsAreCompatibleWithLearner($dataset, $this)])->check();
        $this->featureCount = $dataset->numFeatures();
        $this->tree->grow($dataset);
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
     * @param list<string|int|float> $sample
     * @return int|float
     */
    public function predictSample(array $sample)
    {
        [$samples, $labels, $distances] = $this->tree->range($sample, $this->radius);
        if (empty($labels)) {
            return self::OUTLIER_VALUE;
        }
        if ($this->weighted) {
            $weights = [];
            foreach ($distances as $distance) {
                $weights[] = 1.0 / (1.0 + $distance);
            }
            return Stats::weightedMean($labels, $weights);
        }
        return Stats::mean($labels);
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
        return 'Radius Neighbors Regressor (' . Params::stringify($this->params()) . ')';
    }
}
