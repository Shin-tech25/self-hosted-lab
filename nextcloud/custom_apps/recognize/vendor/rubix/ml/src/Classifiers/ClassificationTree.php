<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Classifiers;

use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\Probabilistic;
use OCA\Recognize\Vendor\Rubix\ML\RanksFeatures;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\Helpers\Params;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
use OCA\Recognize\Vendor\Rubix\ML\Graph\Trees\CART;
use OCA\Recognize\Vendor\Rubix\ML\Graph\Nodes\Best;
use OCA\Recognize\Vendor\Rubix\ML\Traits\AutotrackRevisions;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsLabeled;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsNotEmpty;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SpecificationChain;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetHasDimensionality;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\LabelsAreCompatibleWithLearner;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SamplesAreCompatibleWithEstimator;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use function OCA\Recognize\Vendor\Rubix\ML\argmax;
use function count;
use function array_fill;
use function array_combine;
use function array_replace;
use function array_count_values;
use function array_map;
/**
 * Classification Tree
 *
 * A binary tree-based learner that greedily constructs a decision map for classification
 * that minimizes the Gini impurity among the training labels within the leaf nodes.
 * Classification Trees also serve as the base learner of ensemble methods such as
 * Random Forest and AdaBoost.
 *
 * References:
 * [1] W. Y. Loh. (2011). Classification and Regression Trees.
 * [2] K. Alsabti. et al. (1998). CLOUDS: A Decision Tree Classifier for Large Datasets.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class ClassificationTree extends CART implements Estimator, Learner, Probabilistic, RanksFeatures, Persistable
{
    use AutotrackRevisions;
    /**
     * The list of possible class outcomes.
     *
     * @var list<string>
     */
    protected array $classes = [];
    /**
     * @param int $maxHeight
     * @param int $maxLeafSize
     * @param float $minPurityIncrease
     * @param int|null $maxFeatures
     * @param int|null $maxBins
     */
    public function __construct(int $maxHeight = \PHP_INT_MAX, int $maxLeafSize = 3, float $minPurityIncrease = 1.0E-7, ?int $maxFeatures = null, ?int $maxBins = null)
    {
        parent::__construct($maxHeight, $maxLeafSize, $minPurityIncrease, $maxFeatures, $maxBins);
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
        return EstimatorType::classifier();
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
        return ['max height' => $this->maxHeight, 'max leaf size' => $this->maxLeafSize, 'min purity increase' => $this->minPurityIncrease, 'max features' => $this->maxFeatures, 'max bins' => $this->maxBins];
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
     * Train the learner with a dataset.
     *
     * @param Labeled $dataset
     */
    public function train(Dataset $dataset) : void
    {
        SpecificationChain::with([new DatasetIsLabeled($dataset), new DatasetIsNotEmpty($dataset), new SamplesAreCompatibleWithEstimator($dataset, $this), new LabelsAreCompatibleWithLearner($dataset, $this)])->check();
        $this->classes = $dataset->possibleOutcomes();
        $this->grow($dataset);
    }
    /**
     * Make predictions from a dataset.
     *
     * @param Dataset $dataset
     * @throws RuntimeException
     * @return list<string>
     */
    public function predict(Dataset $dataset) : array
    {
        if ($this->bare() or !$this->featureCount) {
            throw new RuntimeException('Estimator has not been trained.');
        }
        DatasetHasDimensionality::with($dataset, $this->featureCount)->check();
        return array_map([$this, 'predictSample'], $dataset->samples());
    }
    /**
     * Predict a single sample and return the result.
     *
     * @internal
     *
     * @param list<string|int|float> $sample
     * @return string
     */
    public function predictSample(array $sample) : string
    {
        /** @var Best $node */
        $node = $this->search($sample);
        return $node->outcome();
    }
    /**
     * Estimate the joint probabilities for each possible outcome.
     *
     * @param Dataset $dataset
     * @throws RuntimeException
     * @return list<array<string,float>>
     */
    public function proba(Dataset $dataset) : array
    {
        if ($this->bare() or !isset($this->featureCount, $this->classes)) {
            throw new RuntimeException('Estimator has not been trained.');
        }
        DatasetHasDimensionality::with($dataset, $this->featureCount)->check();
        $template = array_combine($this->classes, array_fill(0, count($this->classes), 0.0)) ?: [];
        $probabilities = [];
        foreach ($dataset->samples() as $sample) {
            /** @var Best $node */
            $node = $this->search($sample);
            $probabilities[] = array_replace($template, $node->probabilities());
        }
        return $probabilities;
    }
    /**
     * Terminate the branch by selecting the class outcome with the highest
     * probability.
     *
     * @param Labeled $dataset
     * @return Best
     */
    protected function terminate(Labeled $dataset) : Best
    {
        $n = $dataset->numSamples();
        $counts = array_count_values($dataset->labels());
        /** @var string $outcome */
        $outcome = argmax($counts);
        $probabilities = [];
        foreach ($counts as $class => $count) {
            $probabilities[$class] = $count / $n;
        }
        $impurity = 1.0 - ($counts[$outcome] / $n) ** 2;
        return new Best($outcome, $probabilities, $impurity, $n);
    }
    /**
     * Calculate the impurity of a set of labels.
     *
     * @param list<string|int> $labels
     * @return float
     */
    protected function impurity(array $labels) : float
    {
        $n = count($labels);
        if ($n <= 1) {
            return 0.0;
        }
        $counts = array_count_values($labels);
        $gini = 0.0;
        foreach ($counts as $count) {
            $gini += 1.0 - ($count / $n) ** 2;
        }
        return $gini;
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
        return 'Classification Tree (' . Params::stringify($this->params()) . ')';
    }
}
