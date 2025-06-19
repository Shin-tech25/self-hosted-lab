<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Regressors;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Tensor\Vector;
use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\RanksFeatures;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\Helpers\Params;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Traits\AutotrackRevisions;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsLabeled;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsNotEmpty;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SpecificationChain;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetHasDimensionality;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\LabelsAreCompatibleWithLearner;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SamplesAreCompatibleWithEstimator;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use function is_null;
/**
 * Ridge
 *
 * L2 regularized least squares linear model solved using a closed-form solution. The addition
 * of regularization, controlled by the *l2Penalty* parameter, makes Ridge less prone to overfitting
 * than ordinary linear regression.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class Ridge implements Estimator, Learner, RanksFeatures, Persistable
{
    use AutotrackRevisions;
    /**
     * The strength of the L2 regularization penalty.
     *
     * @var float
     */
    protected float $l2Penalty;
    /**
     * The y intercept i.e. the bias added to the decision function.
     *
     * @var float|null
     */
    protected ?float $bias = null;
    /**
     * The computed coefficients of the regression line.
     *
     * @var Vector|null
     */
    protected ?Vector $coefficients = null;
    /**
     * @param float $l2Penalty
     * @throws InvalidArgumentException
     */
    public function __construct(float $l2Penalty = 1.0)
    {
        if ($l2Penalty < 0.0) {
            throw new InvalidArgumentException('L2 Penalty must be' . " greater than 0, {$l2Penalty} given.");
        }
        $this->l2Penalty = $l2Penalty;
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
        return ['l2 penalty' => $this->l2Penalty];
    }
    /**
     * Has the learner been trained?
     *
     * @return bool
     */
    public function trained() : bool
    {
        return $this->coefficients and isset($this->bias);
    }
    /**
     * Return the weights of features in the decision function.
     *
     * @return (int|float)[]|null
     */
    public function coefficients() : ?array
    {
        return $this->coefficients ? $this->coefficients->asArray() : null;
    }
    /**
     * Return the bias added to the decision function.
     *
     * @return float|null
     */
    public function bias() : ?float
    {
        return $this->bias;
    }
    /**
     * Train the learner with a dataset.
     *
     * @param \OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled $dataset
     */
    public function train(Dataset $dataset) : void
    {
        SpecificationChain::with([new DatasetIsLabeled($dataset), new DatasetIsNotEmpty($dataset), new SamplesAreCompatibleWithEstimator($dataset, $this), new LabelsAreCompatibleWithLearner($dataset, $this)])->check();
        $biases = Matrix::ones($dataset->numSamples(), 1);
        $x = Matrix::build($dataset->samples())->augmentLeft($biases);
        $y = Vector::build($dataset->labels());
        /** @var int<0,max> $nHat */
        $nHat = $x->n() - 1;
        $penalties = \array_fill(0, $nHat, $this->l2Penalty);
        \array_unshift($penalties, 0.0);
        $penalties = Matrix::diagonal($penalties);
        $xT = $x->transpose();
        $coefficients = $xT->matmul($x)->add($penalties)->inverse()->dot($xT->dot($y))->asArray();
        $this->bias = (float) \array_shift($coefficients);
        $this->coefficients = Vector::quick($coefficients);
    }
    /**
     * Make a prediction based on the line calculated from the training data.
     *
     * @param Dataset $dataset
     * @throws RuntimeException
     * @return list<int|float>
     */
    public function predict(Dataset $dataset) : array
    {
        if (!$this->coefficients or is_null($this->bias)) {
            throw new RuntimeException('Estimator has not been trained.');
        }
        DatasetHasDimensionality::with($dataset, \count($this->coefficients))->check();
        return Matrix::build($dataset->samples())->dot($this->coefficients)->add($this->bias)->asArray();
    }
    /**
     * Return the importance scores of each feature column of the training set.
     *
     * @throws RuntimeException
     * @return float[]
     */
    public function featureImportances() : array
    {
        if (is_null($this->coefficients)) {
            throw new RuntimeException('Learner has not been trained.');
        }
        return $this->coefficients->abs()->asArray();
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
        return 'Ridge (' . Params::stringify($this->params()) . ')';
    }
}
