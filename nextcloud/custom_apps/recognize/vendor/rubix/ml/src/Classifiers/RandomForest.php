<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Classifiers;

use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\Parallel;
use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\Probabilistic;
use OCA\Recognize\Vendor\Rubix\ML\RanksFeatures;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\Helpers\Params;
use OCA\Recognize\Vendor\Rubix\ML\Backends\Serial;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Backends\Tasks\Proba;
use OCA\Recognize\Vendor\Rubix\ML\Traits\Multiprocessing;
use OCA\Recognize\Vendor\Rubix\ML\Backends\Tasks\Predict;
use OCA\Recognize\Vendor\Rubix\ML\Backends\Tasks\TrainLearner;
use OCA\Recognize\Vendor\Rubix\ML\Traits\AutotrackRevisions;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsLabeled;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsNotEmpty;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SpecificationChain;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetHasDimensionality;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\LabelsAreCompatibleWithLearner;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SamplesAreCompatibleWithEstimator;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use function OCA\Recognize\Vendor\Rubix\ML\argmax;
use function OCA\Recognize\Vendor\Rubix\ML\array_transpose;
use function array_count_values;
use function get_class;
use function in_array;
/**
 * Random Forest
 *
 * An ensemble classifier that trains an ensemble of Decision Trees (Classification or Extra Trees)
 * on random subsets (*bootstrap* set) of the training data. Predictions are based on the
 * probability scores returned from each tree in the forest, averaged and weighted equally.
 *
 * References:
 * [1] L. Breiman. (2001). Random Forests.
 * [2] L. Breiman et al. (2005). Extremely Randomized Trees.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class RandomForest implements Estimator, Learner, Probabilistic, Parallel, RanksFeatures, Persistable
{
    use AutotrackRevisions, Multiprocessing;
    /**
     * The class names of the learners that are compatible with the ensemble.
     *
     * @var class-string[]
     */
    public const COMPATIBLE_LEARNERS = [ClassificationTree::class, ExtraTreeClassifier::class];
    /**
     * The minimum size of each training subset.
     *
     * @var int
     */
    protected const MIN_SUBSAMPLE = 1;
    /**
     * The base learner.
     *
     * @var Learner
     */
    protected Learner $base;
    /**
     * The number of learners to train in the ensemble.
     *
     * @var int
     */
    protected int $estimators;
    /**
     * The ratio of samples from the training set to randomly subsample to train each base learner.
     *
     * @var float
     */
    protected float $ratio;
    /**
     * Should we sample the bootstrap set to compensate for imbalanced class labels?
     *
     * @var bool
     */
    protected bool $balanced;
    /**
     * The decision trees that make up the forest.
     *
     * @var list<ClassificationTree|ExtraTreeClassifier>|null
     */
    protected ?array $trees = null;
    /**
     * The zero vector for the possible class outcomes.
     *
     * @var float[]|null
     */
    protected ?array $classes = null;
    /**
     * The dimensionality of the training set.
     *
     * @var int<0,max>|null
     */
    protected ?int $featureCount = null;
    /**
     * @param Learner|null $base
     * @param int $estimators
     * @param float $ratio
     * @param bool $balanced
     * @throws InvalidArgumentException
     */
    public function __construct(?Learner $base = null, int $estimators = 100, float $ratio = 0.2, bool $balanced = \false)
    {
        if ($base and !in_array(get_class($base), self::COMPATIBLE_LEARNERS)) {
            throw new InvalidArgumentException('Base Learner must be' . ' compatible with ensemble.');
        }
        if ($estimators < 1) {
            throw new InvalidArgumentException('Number of estimators' . " must be greater than 0, {$estimators} given.");
        }
        if ($ratio <= 0.0 or $ratio > 1.5) {
            throw new InvalidArgumentException('Ratio must be between' . " 0 and 1.5, {$ratio} given.");
        }
        $this->base = $base ?? new ClassificationTree();
        $this->estimators = $estimators;
        $this->ratio = $ratio;
        $this->balanced = $balanced;
        $this->backend = new Serial();
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
        return $this->base->compatibility();
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
        return ['base' => $this->base, 'estimators' => $this->estimators, 'ratio' => $this->ratio, 'balanced' => $this->balanced];
    }
    /**
     * Has the learner been trained?
     *
     * @return bool
     */
    public function trained() : bool
    {
        return !empty($this->trees);
    }
    /**
     * Train the learner with a dataset.
     *
     * @param \OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled $dataset
     */
    public function train(Dataset $dataset) : void
    {
        SpecificationChain::with([new DatasetIsLabeled($dataset), new DatasetIsNotEmpty($dataset), new SamplesAreCompatibleWithEstimator($dataset, $this), new LabelsAreCompatibleWithLearner($dataset, $this)])->check();
        $p = \max(self::MIN_SUBSAMPLE, (int) \ceil($this->ratio * $dataset->numSamples()));
        if ($this->balanced) {
            $counts = array_count_values($dataset->labels());
            $min = \min($counts);
            $weights = [];
            foreach ($dataset->labels() as $label) {
                $weights[] = $min / $counts[$label];
            }
        }
        $this->backend->flush();
        for ($i = 0; $i < $this->estimators; ++$i) {
            $estimator = clone $this->base;
            if (isset($weights)) {
                $subset = $dataset->randomWeightedSubsetWithReplacement($p, $weights);
            } else {
                $subset = $dataset->randomSubsetWithReplacement($p);
            }
            $this->backend->enqueue(new TrainLearner($estimator, $subset));
        }
        $this->trees = $this->backend->process();
        $this->classes = \array_fill_keys($dataset->possibleOutcomes(), 0.0);
        $this->featureCount = $dataset->numFeatures();
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
        if (!$this->trees or !$this->featureCount) {
            throw new RuntimeException('Estimator has not been trained.');
        }
        DatasetHasDimensionality::with($dataset, $this->featureCount)->check();
        $this->backend->flush();
        foreach ($this->trees as $estimator) {
            $this->backend->enqueue(new Predict($estimator, $dataset));
        }
        $aggregate = array_transpose($this->backend->process());
        $predictions = [];
        foreach ($aggregate as $votes) {
            /** @var array<string,int> $counts */
            $counts = array_count_values($votes);
            $predictions[] = argmax($counts);
        }
        return $predictions;
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
        if (!$this->trees or !$this->classes or !$this->featureCount) {
            throw new RuntimeException('Estimator has not been trained.');
        }
        DatasetHasDimensionality::with($dataset, $this->featureCount)->check();
        $probabilities = \array_fill(0, $dataset->numSamples(), $this->classes);
        $this->backend->flush();
        foreach ($this->trees as $estimator) {
            $this->backend->enqueue(new Proba($estimator, $dataset));
        }
        $aggregate = $this->backend->process();
        foreach ($aggregate as $proba) {
            /** @var int $i */
            foreach ($proba as $i => $joint) {
                foreach ($joint as $class => $probability) {
                    $probabilities[$i][$class] += $probability;
                }
            }
        }
        foreach ($probabilities as &$joint) {
            foreach ($joint as &$probability) {
                $probability /= $this->estimators;
            }
        }
        return $probabilities;
    }
    /**
     * Return the importance scores of each feature column of the training set.
     *
     * @throws RuntimeException
     * @return float[]
     */
    public function featureImportances() : array
    {
        if (!$this->trees or !$this->featureCount) {
            throw new RuntimeException('Estimator has not been trained.');
        }
        $importances = \array_fill(0, $this->featureCount, 0.0);
        foreach ($this->trees as $tree) {
            foreach ($tree->featureImportances() as $column => $importance) {
                $importances[$column] += $importance;
            }
        }
        foreach ($importances as &$importance) {
            $importance /= $this->estimators;
        }
        return $importances;
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
        return 'Random Forest (' . Params::stringify($this->params()) . ')';
    }
}
