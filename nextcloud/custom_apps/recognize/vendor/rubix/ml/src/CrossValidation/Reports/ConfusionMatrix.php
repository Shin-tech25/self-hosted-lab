<?php

namespace OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Reports;

use OCA\Recognize\Vendor\Rubix\ML\Report;
use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\PredictionAndLabelCountsAreEqual;
use function array_fill_keys;
use function array_merge;
use function array_unique;
/**
 * Confusion Matrix
 *
 * A Confusion Matrix is a square matrix (table) that visualizes the true positives, false positives,
 * true negatives, and false negatives of a set of class predictions and their corresponding labels.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class ConfusionMatrix implements ReportGenerator
{
    /**
     * The estimator types that this report is compatible with.
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
     * Generate the report.
     *
     * @param list<string|int> $predictions
     * @param list<string|int> $labels
     * @return Report
     */
    public function generate(array $predictions, array $labels) : Report
    {
        PredictionAndLabelCountsAreEqual::with($predictions, $labels)->check();
        $classes = array_unique(array_merge($predictions, $labels));
        $matrix = array_fill_keys($classes, array_fill_keys($classes, 0));
        foreach ($predictions as $i => $prediction) {
            ++$matrix[$prediction][$labels[$i]];
        }
        return new Report($matrix);
    }
}
