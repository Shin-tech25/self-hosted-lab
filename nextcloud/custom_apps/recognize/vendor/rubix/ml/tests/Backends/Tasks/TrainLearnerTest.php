<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Backends\Tasks;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Classifiers\GaussianNB;
use OCA\Recognize\Vendor\Rubix\ML\Backends\Tasks\TrainLearner;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Agglomerate;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Tasks
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Backends\Tasks\TrainLearner
 * @internal
 */
class TrainLearnerTest extends TestCase
{
    /**
     * @test
     */
    public function compute() : void
    {
        $estimator = new GaussianNB();
        $generator = new Agglomerate(['male' => new Blob([69.2, 195.7, 40.0], [1.0, 3.0, 0.3]), 'female' => new Blob([63.7, 168.5, 38.1], [0.8, 2.5, 0.4])], [0.45, 0.55]);
        $dataset = $generator->generate(50);
        $task = new TrainLearner($estimator, $dataset);
        $result = $task->compute();
        $this->assertInstanceOf(GaussianNB::class, $result);
        $this->assertTrue($result->trained());
    }
}
