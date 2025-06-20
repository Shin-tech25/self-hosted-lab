<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Clusterers;

use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\Helpers\Params;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
use OCA\Recognize\Vendor\Rubix\ML\Graph\Trees\Spatial;
use OCA\Recognize\Vendor\Rubix\ML\Graph\Trees\BallTree;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\Distance\Distance;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsNotEmpty;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SpecificationChain;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SamplesAreCompatibleWithEstimator;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use function count;
use function array_unique;
use function array_merge;
use function array_pop;
/**
 * DBSCAN
 *
 * *Density-Based Spatial Clustering of Applications with Noise* is a clustering algorithm
 * able to find non-linearly separable and arbitrarily-shaped clusters given a radius and
 * density constraint. In addition, DBSCAN also has the ability to mark outliers as *noise*
 * and thus can be used as a *quasi* anomaly detector.
 *
 * > **Note**: Noise samples are assigned to the cluster number *-1*.
 *
 * References:
 * [1] M. Ester et al. (1996). A Density-Based Algorithm for Discovering Clusters.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class DBSCAN implements Estimator
{
    /**
     * The starting cluster number.
     *
     * @var int
     */
    public const START_CLUSTER = 0;
    /**
     * The cluster number assigned to noise samples.
     *
     * @var int
     */
    public const NOISE = -1;
    /**
     * The maximum distance between two points to be considered neighbors. The smaller the value,
     * the tighter the clusters will be.
     *
     * @var float
     */
    protected float $radius;
    /**
     * The minimum number of points to from a dense region or cluster.
     *
     * @var int
     */
    protected int $minDensity;
    /**
     * The spatial tree used to run range searches.
     *
     * @var Spatial
     */
    protected Spatial $tree;
    /**
     * @param float $radius
     * @param int $minDensity
     * @param Spatial|null $tree
     * @throws InvalidArgumentException
     */
    public function __construct(float $radius = 0.5, int $minDensity = 5, ?Spatial $tree = null)
    {
        if ($radius <= 0.0) {
            throw new InvalidArgumentException('Radius must be' . " greater than 0, {$radius} given.");
        }
        if ($minDensity <= 0) {
            throw new InvalidArgumentException('Minimum density must be' . " greater than 0, {$minDensity} given.");
        }
        $this->radius = $radius;
        $this->minDensity = $minDensity;
        $this->tree = $tree ?? new BallTree();
    }
    /**
     * Return the estimator type.
     *
     * @return EstimatorType
     */
    public function type() : EstimatorType
    {
        return EstimatorType::clusterer();
    }
    /**
     * Return the data types that the estimator is compatible with.
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
     * @return mixed[]
     */
    public function params() : array
    {
        return ['radius' => $this->radius, 'min density' => $this->minDensity, 'tree' => $this->tree];
    }
    /**
     * Make predictions from a dataset.
     *
     * @param Dataset $dataset
     * @return list<int>
     */
    public function predict(Dataset $dataset) : array
    {
        SpecificationChain::with([new DatasetIsNotEmpty($dataset), new SamplesAreCompatibleWithEstimator($dataset, $this)])->check();
        $labels = \range(0, $dataset->numSamples() - 1);
        $dataset = Labeled::quick($dataset->samples(), $labels);
        $this->tree->grow($dataset);
        $cluster = self::START_CLUSTER;
        $predictions = [];
        foreach ($dataset->samples() as $i => $sample) {
            if (isset($predictions[$i])) {
                continue;
            }
            [$samples, $indices, $distances] = $this->tree->range($sample, $this->radius);
            if (count($samples) < $this->minDensity) {
                $predictions[$i] = self::NOISE;
                continue;
            }
            $predictions[$i] = $cluster;
            while ($indices) {
                $index = (int) array_pop($indices);
                if (isset($predictions[$index])) {
                    if ($predictions[$index] === self::NOISE) {
                        $predictions[$index] = $cluster;
                    }
                    continue;
                }
                $predictions[$index] = $cluster;
                $neighbor = $dataset->sample($index);
                [$samples, $seeds, $distances] = $this->tree->range($neighbor, $this->radius);
                if (count($seeds) >= $this->minDensity) {
                    $indices = array_unique(array_merge($indices, $seeds));
                }
            }
            ++$cluster;
        }
        $this->tree->destroy();
        return $predictions;
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
        return 'DBSCAN (' . Params::stringify($this->params()) . ')';
    }
}
