<?php

namespace OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics;

use OCA\Recognize\Vendor\Rubix\ML\Tuple;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Reports\ContingencyTable;
use function count;
use const OCA\Recognize\Vendor\Rubix\ML\EPSILON;
/**
 * Completeness
 *
 * A ground-truth clustering metric that measures the ratio of samples in a class that
 * are also members of the same cluster. A cluster is said to be *complete* when all the
 * samples in a class are contained in a cluster.
 *
 * References:
 * [1] A. Rosenberg et al. (2007). V-Measure: A conditional entropy-based
 * external cluster evaluation measure.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class Completeness implements Metric
{
    /**
     * Return a tuple of the min and max output value for this metric.
     *
     * @return \OCA\Recognize\Vendor\Rubix\ML\Tuple{float,float}
     */
    public function range() : Tuple
    {
        return new Tuple(0.0, 1.0);
    }
    /**
     * The estimator types that this metric is compatible with.
     *
     * @internal
     *
     * @return list<\OCA\Recognize\Vendor\Rubix\ML\EstimatorType>
     */
    public function compatibility() : array
    {
        return [EstimatorType::clusterer()];
    }
    /**
     * Score a set of predictions.
     *
     * @param list<string|int> $predictions
     * @param list<string|int> $labels
     * @return float
     */
    public function score(array $predictions, array $labels) : float
    {
        $table = (new ContingencyTable())->generate($labels, $predictions);
        $score = 0.0;
        foreach ($table as $dist) {
            $score += \max($dist) / (\array_sum($dist) ?: EPSILON);
        }
        return $score / count($table);
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
        return 'Completeness';
    }
}
