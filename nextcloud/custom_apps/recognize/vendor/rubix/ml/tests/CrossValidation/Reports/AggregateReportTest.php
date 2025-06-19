<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\CrossValidation\Reports;

use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\Report;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Reports\ReportGenerator;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Reports\ConfusionMatrix;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Reports\AggregateReport;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Reports\MulticlassBreakdown;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Reports
 * @covers \OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Reports\AggregateReport
 * @internal
 */
class AggregateReportTest extends TestCase
{
    /**
     * @var AggregateReport
     */
    protected $report;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->report = new AggregateReport(['matrix' => new ConfusionMatrix(), 'breakdown' => new MulticlassBreakdown()]);
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(AggregateReport::class, $this->report);
        $this->assertInstanceOf(ReportGenerator::class, $this->report);
    }
    /**
     * @test
     */
    public function compatibility() : void
    {
        $expected = [EstimatorType::classifier(), EstimatorType::anomalyDetector()];
        $this->assertEquals($expected, $this->report->compatibility());
    }
    /**
     * @test
     */
    public function generate() : void
    {
        $predictions = ['wolf', 'lamb', 'wolf', 'lamb', 'wolf'];
        $labels = ['lamb', 'lamb', 'wolf', 'wolf', 'wolf'];
        $result = $this->report->generate($predictions, $labels);
        $this->assertInstanceOf(Report::class, $result);
        $this->assertCount(2, $result->toArray());
    }
}
