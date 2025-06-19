<?php

namespace OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics;

use OCA\Recognize\Vendor\Rubix\ML\Tuple;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\Helpers\Stats;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\PredictionAndLabelCountsAreEqual;
use const OCA\Recognize\Vendor\Rubix\ML\EPSILON;
/**
 * Informedness
 *
 * Informedness a multiclass generalization of Youden's J Statistic and can be interpreted as the
 * probability that an estimator will make an informed prediction. Its value ranges from -1 through
 * 1 and has a value of 0 when the test yields no useful information.
 *
 * References:
 * [1] W. J. Youden. (1950). Index for Rating Diagnostic Tests.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class Informedness implements Metric
{
    /**
     * Compute the class Informedness score.
     *
     * @internal
     *
     * @param int $tp
     * @param int $tn
     * @param int $fp
     * @param int $fn
     * @return float
     */
    public static function compute(int $tp, int $tn, int $fp, int $fn) : float
    {
        return $tp / ($tp + $fn ?: EPSILON) + $tn / ($tn + $fp ?: EPSILON) - 1.0;
    }
    /**
     * Return a tuple of the min and max output value for this metric.
     *
     * @return \OCA\Recognize\Vendor\Rubix\ML\Tuple{float,float}
     */
    public function range() : Tuple
    {
        return new Tuple(-1.0, 1.0);
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
        return [EstimatorType::classifier(), EstimatorType::anomalyDetector()];
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
        PredictionAndLabelCountsAreEqual::with($predictions, $labels)->check();
        if (empty($predictions)) {
            return 0.0;
        }
        $classes = \array_unique(\array_merge($predictions, $labels));
        $truePos = $trueNeg = $falsePos = $falseNeg = \array_fill_keys($classes, 0);
        foreach ($predictions as $i => $prediction) {
            $label = $labels[$i];
            if ($prediction == $label) {
                ++$truePos[$prediction];
                foreach ($classes as $class) {
                    if ($class != $prediction) {
                        ++$trueNeg[$class];
                    }
                }
            } else {
                ++$falsePos[$prediction];
                ++$falseNeg[$label];
            }
        }
        $scores = \array_map([self::class, 'compute'], $truePos, $trueNeg, $falsePos, $falseNeg);
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
        return 'Informedness';
    }
}
