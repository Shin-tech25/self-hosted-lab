<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Regressors;

use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\RanksFeatures;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\Helpers\Stats;
use OCA\Recognize\Vendor\Rubix\ML\Helpers\Params;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
use OCA\Recognize\Vendor\Rubix\ML\Graph\Nodes\Average;
use OCA\Recognize\Vendor\Rubix\ML\Graph\Trees\ExtraTree;
use OCA\Recognize\Vendor\Rubix\ML\Traits\AutotrackRevisions;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsLabeled;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsNotEmpty;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SpecificationChain;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetHasDimensionality;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\LabelsAreCompatibleWithLearner;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SamplesAreCompatibleWithEstimator;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
/**
 * Extra Tree Regressor
 *
 * An *Extremely Randomized* Regression Tree. These trees differ from standard Regression
 * Trees in that they choose candidate splits at random, rather than searching the entire
 * column for the best split. Extra Trees are faster to build and their predictions have
 * higher variance than a regular decision tree.
 *
 * References:
 * [1] P. Geurts. et al. (2005). Extremely Randomized Trees.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class ExtraTreeRegressor extends ExtraTree implements Estimator, Learner, RanksFeatures, Persistable
{
    use AutotrackRevisions;
    /**
     * @param int $maxHeight
     * @param int $maxLeafSize
     * @param float $minPurityIncrease
     * @param int|null $maxFeatures
     */
    public function __construct(int $maxHeight = \PHP_INT_MAX, int $maxLeafSize = 3, float $minPurityIncrease = 1.0E-7, ?int $maxFeatures = null)
    {
        parent::__construct($maxHeight, $maxLeafSize, $minPurityIncrease, $maxFeatures);
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
        return [DataType::categorical(), DataType::continuous()];
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
        return ['max height' => $this->maxHeight, 'max leaf size' => $this->maxLeafSize, 'max features' => $this->maxFeatures, 'min purity increase' => $this->minPurityIncrease];
    }
    /**
     * Has the learner been trained?
     *
     * @return bool
     */
    public function trained() : bool
    {
        return !$this->bare();
    }
    /**
     * Train the regression tree by learning the optimal splits in the
     * training set.
     *
     * @param Labeled $dataset
     */
    public function train(Dataset $dataset) : void
    {
        SpecificationChain::with([new DatasetIsLabeled($dataset), new DatasetIsNotEmpty($dataset), new SamplesAreCompatibleWithEstimator($dataset, $this), new LabelsAreCompatibleWithLearner($dataset, $this)])->check();
        $this->grow($dataset);
    }
    /**
     * Make a prediction based on the value of a terminal node in the tree.
     *
     * @param Dataset $dataset
     * @throws RuntimeException
     * @return list<int|float>
     */
    public function predict(Dataset $dataset) : array
    {
        if ($this->bare() or !$this->featureCount) {
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
        /** @var Average $node */
        $node = $this->search($sample);
        return $node->outcome();
    }
    /**
     * Terminate the branch with the most likely Average.
     *
     * @param Labeled $dataset
     * @return Average
     */
    protected function terminate(Labeled $dataset) : Average
    {
        [$mean, $variance] = Stats::meanVar($dataset->labels());
        return new Average($mean, $variance, $dataset->numSamples());
    }
    /**
     * Calculate the impurity of a set of labels.
     *
     * @param list<int|float> $labels
     * @return float
     */
    protected function impurity(array $labels) : float
    {
        return Stats::variance($labels);
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
        return 'Extra Tree Regressor (' . Params::stringify($this->params()) . ')';
    }
}
