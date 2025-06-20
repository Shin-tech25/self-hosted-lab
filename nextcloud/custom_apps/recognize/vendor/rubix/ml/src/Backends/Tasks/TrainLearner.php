<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Backends\Tasks;

use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
/**
 * Train Learner
 *
 * A routine to train a learner and then return it.
 *
 * @internal
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
class TrainLearner extends Task
{
    /**
     * Train a learner and return the instance.
     *
     * @param Learner $estimator
     * @param Dataset $dataset
     * @return Learner
     */
    public static function train(Learner $estimator, Dataset $dataset) : Learner
    {
        $estimator->train($dataset);
        return $estimator;
    }
    /**
     * @param Learner $estimator
     * @param Dataset $dataset
     */
    public function __construct(Learner $estimator, Dataset $dataset)
    {
        parent::__construct([self::class, 'train'], [$estimator, $dataset]);
    }
}
