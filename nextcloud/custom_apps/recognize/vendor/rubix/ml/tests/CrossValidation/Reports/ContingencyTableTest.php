<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\CrossValidation\Reports;

use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\Report;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Reports\ReportGenerator;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Reports\ContingencyTable;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Reports
 * @covers \OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Reports\ContingencyTable
 * @internal
 */
class ContingencyTableTest extends TestCase
{
    /**
     * @var ContingencyTable
     */
    protected $report;
    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->report = new ContingencyTable();
    }
    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(ContingencyTable::class, $this->report);
        $this->assertInstanceOf(ReportGenerator::class, $this->report);
    }
    /**
     * @test
     */
    public function compatibility() : void
    {
        $expected = [EstimatorType::clusterer()];
        $this->assertEquals($expected, $this->report->compatibility());
    }
    /**
     * @test
     * @dataProvider generateProvider
     *
     * @param (string|int)[] $predictions
     * @param (string|int)[] $labels
     * @param mixed[] $expected
     */
    public function generate(array $predictions, array $labels, array $expected) : void
    {
        $result = $this->report->generate($predictions, $labels);
        $this->assertInstanceOf(Report::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function generateProvider() : Generator
    {
        (yield [[0, 1, 1, 0, 1], ['lamb', 'lamb', 'wolf', 'wolf', 'wolf'], [0 => ['wolf' => 1, 'lamb' => 1], 1 => ['wolf' => 2, 'lamb' => 1]]]);
    }
}
