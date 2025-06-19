<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Specifications;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled;
use OCA\Recognize\Vendor\Rubix\ML\Classifiers\AdaBoost;
use OCA\Recognize\Vendor\Rubix\ML\Regressors\GradientBoost;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\LabelsAreCompatibleWithLearner;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
use Generator;
/**
 * @group Specifications
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Specifications\LabelsAreCompatibleWithLearner
 * @internal
 */
class LabelsAreCompatibleWithLearnerTest extends TestCase
{
    /**
     * @test
     * @dataProvider passesProvider
     *
     * @param LabelsAreCompatibleWithLearner $specification
     * @param bool $expected
     */
    public function passes(LabelsAreCompatibleWithLearner $specification, bool $expected) : void
    {
        $this->assertSame($expected, $specification->passes());
    }
    /**
     * @return \Generator<mixed[]>
     */
    public function passesProvider() : Generator
    {
        (yield [LabelsAreCompatibleWithLearner::with(Labeled::quick([[6.0, -1.1, 5, 'college']], [200]), new GradientBoost()), \true]);
        (yield [LabelsAreCompatibleWithLearner::with(Labeled::quick([[6.0, -1.1, 5, 'college']], ['stormy night']), new AdaBoost()), \true]);
        (yield [LabelsAreCompatibleWithLearner::with(Labeled::quick([[6.0, -1.1, 5, 'college']], ['stormy night']), new GradientBoost()), \false]);
        (yield [LabelsAreCompatibleWithLearner::with(Labeled::quick([[6.0, -1.1, 5, 'college']], [200]), new AdaBoost()), \false]);
    }
}
