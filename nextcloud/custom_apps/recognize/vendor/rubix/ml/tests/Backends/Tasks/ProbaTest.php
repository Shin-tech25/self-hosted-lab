<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Backends\Tasks;

use OCA\Recognize\Vendor\Rubix\ML\Backends\Tasks\Proba;
use OCA\Recognize\Vendor\Rubix\ML\Classifiers\GaussianNB;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Agglomerate;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Tasks
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Backends\Tasks\Proba
 * @internal
 */
class ProbaTest extends TestCase
{
    /**
     * @test
     */
    public function compute() : void
    {
        $estimator = new GaussianNB();
        $generator = new Agglomerate(['male' => new Blob([69.2, 195.7, 40.0], [1.0, 3.0, 0.3]), 'female' => new Blob([63.7, 168.5, 38.1], [0.8, 2.5, 0.4])], [0.45, 0.55]);
        $training = $generator->generate(50);
        $estimator->train($training);
        $testing = $generator->generate(15);
        $task = new Proba($estimator, $testing);
        $result = $task->compute();
        $this->assertCount(15, $result);
    }
}
