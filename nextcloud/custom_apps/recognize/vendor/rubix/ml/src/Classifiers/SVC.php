<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Classifiers;

use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\Helpers\Params;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\SVM\RBF;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Kernels\SVM\Kernel;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsLabeled;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\ExtensionIsLoaded;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsNotEmpty;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SpecificationChain;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\ExtensionMinimumVersion;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\LabelsAreCompatibleWithLearner;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SamplesAreCompatibleWithEstimator;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use svmmodel;
use svm;
/**
 * SVC
 *
 * The multiclass Support Vector Machine (SVM) Classifier is a maximum margin classifier
 * that can efficiently perform non-linear classification by implicitly mapping feature
 * vectors into high-dimensional feature space using the *kernel trick*.
 *
 * > **Note:** This estimator requires the SVM extension which uses the libsvm engine
 * under the hood.
 *
 * References:
 * [1] C. Chang et al. (2011). LIBSVM: A library for support vector machines.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class SVC implements Estimator, Learner
{
    /**
     * The support vector machine instance.
     *
     * @var svm
     */
    protected $svm;
    /**
     * The memoized hyper-parameters of the model.
     *
     * @var mixed[]
     */
    protected $params;
    /**
     * The trained model instance.
     *
     * @var svmmodel|null
     */
    protected $model;
    /**
     * The mappings from integer to class label.
     *
     * @var string[]
     */
    protected $classes = [];
    /**
     * @param float $c
     * @param Kernel|null $kernel
     * @param bool $shrinking
     * @param float $tolerance
     * @param float $cacheSize
     * @throws InvalidArgumentException
     */
    public function __construct(float $c = 1.0, ?Kernel $kernel = null, bool $shrinking = \true, float $tolerance = 0.001, float $cacheSize = 100.0)
    {
        SpecificationChain::with([new ExtensionIsLoaded('svm'), new ExtensionMinimumVersion('svm', '0.2.0')])->check();
        if ($c < 0.0) {
            throw new InvalidArgumentException('C must be greater' . " than 0, {$c} given.");
        }
        $kernel = $kernel ?? new RBF();
        if ($tolerance < 0.0) {
            throw new InvalidArgumentException('Tolerance must be' . " greater than 0, {$tolerance} given.");
        }
        if ($cacheSize <= 0.0) {
            throw new InvalidArgumentException('Cache size must be' . " greater than 0M, {$cacheSize}M given.");
        }
        $options = [svm::OPT_TYPE => svm::C_SVC, svm::OPT_C => $c, svm::OPT_SHRINKING => $shrinking, svm::OPT_EPS => $tolerance, svm::OPT_CACHE_SIZE => $cacheSize];
        $options += $kernel->options();
        $svm = new svm();
        $svm->setOptions($options);
        $this->svm = $svm;
        $this->params = ['c' => $c, 'kernel' => $kernel, 'shrinking' => $shrinking, 'tolerance' => $tolerance, 'cache size' => $cacheSize];
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
        return [DataType::continuous()];
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
        return $this->params;
    }
    /**
     * Has the learner been trained?
     *
     * @return bool
     */
    public function trained() : bool
    {
        return isset($this->model);
    }
    /**
     * Train the learner with a dataset.
     *
     * @param \OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled $dataset
     */
    public function train(Dataset $dataset) : void
    {
        SpecificationChain::with([new DatasetIsLabeled($dataset), new DatasetIsNotEmpty($dataset), new SamplesAreCompatibleWithEstimator($dataset, $this), new LabelsAreCompatibleWithLearner($dataset, $this)])->check();
        $this->classes = $dataset->possibleOutcomes();
        $mapping = \array_flip($this->classes);
        $labels = $dataset->labels();
        $data = [];
        foreach ($dataset->samples() as $i => $sample) {
            $data[] = \array_merge([$mapping[$labels[$i]]], $sample);
        }
        $this->model = $this->svm->train($data);
    }
    /**
     * Make predictions from a dataset.
     *
     * @param Dataset $dataset
     * @return list<string>
     */
    public function predict(Dataset $dataset) : array
    {
        return \array_map([$this, 'predictSample'], $dataset->samples());
    }
    /**
     * Predict a single sample and return the result.
     *
     * @internal
     *
     * @param list<int|float> $sample
     * @throws RuntimeException
     * @return string
     */
    public function predictSample(array $sample) : string
    {
        if (!$this->model) {
            throw new RuntimeException('Estimator has not been trained.');
        }
        $sampleWithOffset = [];
        foreach ($sample as $key => $value) {
            $sampleWithOffset[$key + 1] = $value;
        }
        $index = $this->model->predict($sampleWithOffset);
        return $this->classes[$index];
    }
    /**
     * Save the model data to the filesystem.
     *
     * @param string $path
     * @throws RuntimeException
     */
    public function save(string $path) : void
    {
        if (!$this->model) {
            throw new RuntimeException('Learner must be trained before saving.');
        }
        $this->model->save($path);
    }
    /**
     * Load model data from the filesystem.
     *
     * @param string $path
     */
    public function load(string $path) : void
    {
        $this->model = new svmmodel($path);
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
        return 'SVC (' . Params::stringify($this->params()) . ')';
    }
}
