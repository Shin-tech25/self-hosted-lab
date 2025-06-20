<?php

namespace OCA\Recognize\Vendor\Rubix\ML\CrossValidation;

use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\Parallel;
use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\Helpers\Stats;
use OCA\Recognize\Vendor\Rubix\ML\Backends\Serial;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Traits\Multiprocessing;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\Metric;
use OCA\Recognize\Vendor\Rubix\ML\Backends\Tasks\TrainAndValidate;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\EstimatorIsCompatibleWithMetric;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
/**
 * K Fold
 *
 * K Fold is a cross validation technique that splits the training set into *k* individual
 * folds and for each training round uses 1 of the folds to test the model and the rest as
 * training data. The final score is the average validation score over all of the *k*
 * rounds. K Fold has the advantage of both training and testing on each sample in the
 * dataset at least once.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class KFold implements Validator, Parallel
{
    use Multiprocessing;
    /**
     * The number of folds to split the dataset into.
     *
     * @var int
     */
    protected int $k;
    /**
     * @param int $k
     * @throws InvalidArgumentException
     */
    public function __construct(int $k = 5)
    {
        if ($k < 2) {
            throw new InvalidArgumentException('K must be greater' . " than 1, {$k} given.");
        }
        $this->k = $k;
        $this->backend = new Serial();
    }
    /**
     * Test the estimator with the supplied dataset and return a validation score.
     *
     * @param Learner $estimator
     * @param Labeled $dataset
     * @param Metric $metric
     * @throws InvalidArgumentException
     * @return float
     */
    public function test(Learner $estimator, Labeled $dataset, Metric $metric) : float
    {
        EstimatorIsCompatibleWithMetric::with($estimator, $metric)->check();
        $dataset->randomize();
        $folds = $dataset->labelType()->isCategorical() ? $dataset->stratifiedFold($this->k) : $dataset->fold($this->k);
        $this->backend->flush();
        for ($i = 0; $i < $this->k; ++$i) {
            $training = Labeled::quick();
            foreach ($folds as $j => $fold) {
                if ($i !== $j) {
                    $training = $training->merge($fold);
                }
            }
            $testing = $folds[$i];
            $this->backend->enqueue(new TrainAndValidate($estimator, $training, $testing, $metric));
        }
        $scores = $this->backend->process();
        return Stats::mean($scores);
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
        return "K Fold (k: {$this->k})";
    }
}
