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
 * Leave P Out
 *
 * Leave P Out tests a learner with a unique holdout set of size p for each iteration until
 * all samples have been tested. Although Leave P Out can take long with large datasets and
 * small values of p, it is especially suited for small datasets.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class LeavePOut implements Validator, Parallel
{
    use Multiprocessing;
    /**
     * The number of samples to leave out each round for testing.
     *
     * @var int
     */
    protected int $p;
    /**
     * @param int $p
     * @throws InvalidArgumentException
     */
    public function __construct(int $p = 10)
    {
        if ($p < 1) {
            throw new InvalidArgumentException('P must be greater' . " than 0, {$p} given.");
        }
        $this->p = $p;
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
        $n = (int) \round($dataset->numSamples() / $this->p);
        $this->backend->flush();
        for ($i = 0; $i < $n; ++$i) {
            $training = clone $dataset;
            $testing = $training->splice($i * $this->p, $this->p);
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
        return "Leave P Out (p: {$this->p})";
    }
}
