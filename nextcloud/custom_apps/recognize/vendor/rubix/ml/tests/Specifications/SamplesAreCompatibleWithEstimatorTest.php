<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Specifications;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Classifiers\NaiveBayes;
use OCA\Recognize\Vendor\Rubix\ML\Regressors\RegressionTree;
use OCA\Recognize\Vendor\Rubix\ML\Clusterers\GaussianMixture;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SamplesAreCompatibleWithEstimator;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Specifications
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Specifications\SamplesAreCompatibleWithEstimator
 * @internal
 */
class SamplesAreCompatibleWithEstimatorTest extends TestCase
{
    /**
     * @test
     * @dataProvider passesProvider
     *
     * @param SamplesAreCompatibleWithEstimator $specification
     * @param bool $expected
     */
    public function passes(SamplesAreCompatibleWithEstimator $specification, bool $expected) : void
    {
        $this->assertSame($expected, $specification->passes());
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function passesProvider() : Generator
    {
        (yield [SamplesAreCompatibleWithEstimator::with(Unlabeled::quick([['swamp', 'island', 'black knight', 'counter spell']]), new NaiveBayes()), \true]);
        (yield [SamplesAreCompatibleWithEstimator::with(Unlabeled::quick([[6.0, -1.1, 5, 'college']]), new RegressionTree()), \true]);
        (yield [SamplesAreCompatibleWithEstimator::with(Unlabeled::quick([[6.0, -1.1, 5, 'college']]), new NaiveBayes()), \false]);
        (yield [SamplesAreCompatibleWithEstimator::with(Unlabeled::quick([[6.0, -1.1, 5, 'college']]), new GaussianMixture(3)), \false]);
    }
}
