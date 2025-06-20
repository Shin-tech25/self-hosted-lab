<?php

namespace OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Reports;

use OCA\Recognize\Vendor\Rubix\ML\Report;
use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\PredictionAndLabelCountsAreEqual;
use function array_fill_keys;
use function array_unique;
/**
 * Contingency Table
 *
 * A Contingency Table is used to display the frequency distribution of class labels among
 * a clustering. It is similar to a Confusion Matrix but uses the labels to establish
 * ground-truth for a clustering problem instead.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class ContingencyTable implements ReportGenerator
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
        return [EstimatorType::clusterer()];
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
        $clusters = array_unique($predictions);
        $classes = array_unique($labels);
        $table = array_fill_keys($clusters, array_fill_keys($classes, 0));
        foreach ($predictions as $i => $prediction) {
            ++$table[$prediction][$labels[$i]];
        }
        return new Report($table);
    }
}
