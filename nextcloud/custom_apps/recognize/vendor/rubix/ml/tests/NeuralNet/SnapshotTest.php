<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Tests\NeuralNet;

use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Snapshot;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\FeedForward;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Dense;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Binary;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Activation;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Placeholder1D;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\Stochastic;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\ActivationFunctions\ELU;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\CostFunctions\CrossEntropy;
use OCA\Recognize\Vendor\PHPUnit\Framework\TestCase;
/**
 * @group NeuralNet
 * @covers \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Snapshot
 * @internal
 */
class SnapshotTest extends TestCase
{
    /**
     * @var Snapshot
     */
    protected $snapshot;
    /**
     * @var \OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Network
     */
    protected $network;
    /**
     * @test
     */
    public function take() : void
    {
        $network = new FeedForward(new Placeholder1D(1), [new Dense(10), new Activation(new ELU()), new Dense(5), new Activation(new ELU()), new Dense(1)], new Binary(['yes', 'no'], new CrossEntropy()), new Stochastic());
        $network->initialize();
        $snapshot = Snapshot::take($network);
        $this->assertInstanceOf(Snapshot::class, $snapshot);
    }
}
