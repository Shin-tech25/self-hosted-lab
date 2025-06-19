<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Specifications;

use OCA\Recognize\Vendor\Rubix\ML\Regressors\Ridge;
use OCA\Recognize\Vendor\Rubix\ML\Classifiers\SoftmaxClassifier;
use OCA\Recognize\Vendor\Rubix\ML\AnomalyDetectors\RobustZScore;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\Accuracy;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\VMeasure;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\MeanSquaredError;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\EstimatorIsCompatibleWithMetric;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Specifications
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Specifications\EstimatorIsCompatibleWithMetric
 * @internal
 */
class EstimatorIsCompatibleWithMetricTest extends TestCase
{
    /**
     * @test
     * @dataProvider passesProvider
     *
     * @param EstimatorIsCompatibleWithMetric $specification
     * @param bool $expected
     */
    public function passes(EstimatorIsCompatibleWithMetric $specification, bool $expected) : void
    {
        $this->assertSame($expected, $specification->passes());
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function passesProvider() : Generator
    {
        (yield [EstimatorIsCompatibleWithMetric::with(new SoftmaxClassifier(), new MeanSquaredError()), \false]);
        (yield [EstimatorIsCompatibleWithMetric::with(new SoftmaxClassifier(), new Accuracy()), \true]);
        (yield [EstimatorIsCompatibleWithMetric::with(new RobustZScore(), new Accuracy()), \true]);
        (yield [EstimatorIsCompatibleWithMetric::with(new Ridge(), new VMeasure()), \false]);
        (yield [EstimatorIsCompatibleWithMetric::with(new Ridge(), new MeanSquaredError()), \true]);
    }
}
