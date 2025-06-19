<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\Backends\Tasks;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Classifiers\GaussianNB;
use OCA\Recognize\Vendor\Rubix\ML\Backends\Tasks\TrainAndValidate;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Agglomerate;
use OCA\Recognize\Vendor\Rubix\ML\CrossValidation\Metrics\Accuracy;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group Tasks
 * @covers \OCA\Recognize\Vendor\Rubix\ML\Backends\Tasks\TrainAndValidate
 * @internal
 */
class TrainAndValidateTest extends TestCase
{
    /**
     * @test
     */
    public function compute() : void
    {
        $estimator = new GaussianNB();
        $generator = new Agglomerate(['male' => new Blob([69.2, 195.7, 40.0], [1.0, 3.0, 0.3]), 'female' => new Blob([63.7, 168.5, 38.1], [0.8, 2.5, 0.4])], [0.45, 0.55]);
        $metric = new Accuracy();
        $training = $generator->generate(50);
        $testing = $generator->generate(15);
        $task = new TrainAndValidate($estimator, $training, $testing, $metric);
        $result = $task->compute();
        $this->assertIsFloat($result);
    }
}
