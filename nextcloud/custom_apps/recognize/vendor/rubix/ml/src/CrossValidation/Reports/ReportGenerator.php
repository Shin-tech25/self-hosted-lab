<?php

namespace OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Reports;

use OCA\Recognize\Vendor\Rubix\ML\Report;
/** @internal */
interface ReportGenerator
{
    /**
     * The estimator types that this report is compatible with.
     *
     * @internal
     *
     * @return list<\OCA\Recognize\Vendor\Rubix\ML\EstimatorType>
     */
    public function compatibility() : array;
    /**
     * Generate the report.
     *
     * @param list<string|int|float> $predictions
     * @param list<string|int|float> $labels
     * @return Report
     */
    public function generate(array $predictions, array $labels) : Report;
}
