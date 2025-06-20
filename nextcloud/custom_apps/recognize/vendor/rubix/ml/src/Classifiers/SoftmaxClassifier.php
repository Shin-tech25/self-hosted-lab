<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Classifiers;

use OCA\Recognize\Vendor\Rubix\ML\Online;
use OCA\Recognize\Vendor\Rubix\ML\Learner;
use OCA\Recognize\Vendor\Rubix\ML\Verbose;
use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Estimator;
use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\Probabilistic;
use OCA\Recognize\Vendor\Rubix\ML\EstimatorType;
use OCA\Recognize\Vendor\Rubix\ML\Helpers\Params;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Traits\LoggerAware;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\FeedForward;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Dense;
use OCA\Recognize\Vendor\Rubix\ML\Traits\AutotrackRevisions;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\Adam;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Multiclass;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Layers\Placeholder1D;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Optimizers\Optimizer;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\Initializers\Xavier1;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsLabeled;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetIsNotEmpty;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SpecificationChain;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\CostFunctions\CrossEntropy;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\DatasetHasDimensionality;
use OCA\Recognize\Vendor\Rubix\ML\NeuralNet\CostFunctions\ClassificationLoss;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\LabelsAreCompatibleWithLearner;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SamplesAreCompatibleWithEstimator;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use Generator;
use function is_nan;
use function count;
use function get_object_vars;
use function number_format;
/**
 * Softmax Classifier
 *
 * A multiclass generalization of Logistic Regression using a single layer neural network
 * with a Softmax output layer.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class SoftmaxClassifier implements Estimator, Learner, Online, Probabilistic, Verbose, Persistable
{
    use AutotrackRevisions, LoggerAware;
    /**
     * The number of training samples to process at a time.
     *
     * @var positive-int
     */
    protected int $batchSize;
    /**
     * The gradient descent optimizer used to update the network parameters.
     *
     * @var Optimizer
     */
    protected Optimizer $optimizer;
    /**
     * The amount of L2 regularization applied to the weights of the output layer.
     *
     * @var float
     */
    protected float $l2Penalty;
    /**
     * The maximum number of training epochs. i.e. the number of times to iterate before terminating.
     *
     * @var int<0,max>
     */
    protected int $epochs;
    /**
     * The minimum change in the training loss necessary to continue training.
     *
     * @var float
     */
    protected float $minChange;
    /**
     * The number of epochs without improvement in the training loss to wait before considering an early stop.
     *
     * @var positive-int
     */
    protected int $window;
    /**
     * The function that computes the loss associated with an erroneous activation during training.
     *
     * @var ClassificationLoss
     */
    protected ClassificationLoss $costFn;
    /**
     * The underlying neural network instance.
     *
     * @var FeedForward|null
     */
    protected ?FeedForward $network = null;
    /**
     * The unique class labels.
     *
     * @var string[]|null
     */
    protected ?array $classes = null;
    /**
     * The loss at each epoch from the last training session.
     *
     * @var float[]|null
     */
    protected ?array $losses = null;
    /**
     * @param int $batchSize
     * @param Optimizer|null $optimizer
     * @param float $l2Penalty
     * @param int $epochs
     * @param float $minChange
     * @param int $window
     * @param ClassificationLoss|null $costFn
     * @throws InvalidArgumentException
     */
    public function __construct(int $batchSize = 128, ?Optimizer $optimizer = null, float $l2Penalty = 0.0001, int $epochs = 1000, float $minChange = 0.0001, int $window = 5, ?ClassificationLoss $costFn = null)
    {
        if ($batchSize < 1) {
            throw new InvalidArgumentException('Batch size must be' . " greater than 0, {$batchSize} given.");
        }
        if ($l2Penalty < 0.0) {
            throw new InvalidArgumentException('L2 Penalty must be' . " greater than 0, {$l2Penalty} given.");
        }
        if ($epochs < 0) {
            throw new InvalidArgumentException('Number of epochs' . " must be greater than 0, {$epochs} given.");
        }
        if ($minChange < 0.0) {
            throw new InvalidArgumentException('Minimum change must be' . " greater than 0, {$minChange} given.");
        }
        if ($window < 1) {
            throw new InvalidArgumentException('Window must be' . " greater than 0, {$window} given.");
        }
        $this->batchSize = $batchSize;
        $this->optimizer = $optimizer ?? new Adam();
        $this->l2Penalty = $l2Penalty;
        $this->epochs = $epochs;
        $this->minChange = $minChange;
        $this->window = $window;
        $this->costFn = $costFn ?? new CrossEntropy();
    }
    /**
     * Return the estimator type.
     *
     * @internal
     *
     * @return EstimatorType
     */
    public function type() : EstimatorType
    {
        return EstimatorType::classifier();
    }
    /**
     * Return the data types that the estimator is compatible with.
     *
     * @internal
     *
     * @return list<\OCA\Recognize\Vendor\Rubix\ML\DataType>
     */
    public function compatibility() : array
    {
        return [DataType::continuous()];
    }
    /**
     * Return the settings of the hyper-parameters in an associative array.
     *
     * @internal
     *
     * @return mixed[]
     */
    public function params() : array
    {
        return ['batch size' => $this->batchSize, 'optimizer' => $this->optimizer, 'l2 penalty' => $this->l2Penalty, 'epochs' => $this->epochs, 'min change' => $this->minChange, 'window' => $this->window, 'cost fn' => $this->costFn];
    }
    /**
     * Has the learner been trained?
     *
     * @return bool
     */
    public function trained() : bool
    {
        return $this->network and $this->classes;
    }
    /**
     * Return an iterable progress table with the steps from the last training session.
     *
     * @return \Generator<mixed[]>
     */
    public function steps() : Generator
    {
        if (!$this->losses) {
            return;
        }
        foreach ($this->losses as $epoch => $loss) {
            (yield ['epoch' => $epoch, 'loss' => $loss]);
        }
    }
    /**
     * Return the loss for each epoch of the last training session.
     *
     * @return float[]|null
     */
    public function losses() : ?array
    {
        return $this->losses;
    }
    /**
     * Return the underlying neural network instance or null if not trained.
     *
     * @return FeedForward|null
     */
    public function network() : ?FeedForward
    {
        return $this->network;
    }
    /**
     * Train the learner with a dataset.
     *
     * @param \OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled $dataset
     */
    public function train(Dataset $dataset) : void
    {
        SpecificationChain::with([new DatasetIsLabeled($dataset), new DatasetIsNotEmpty($dataset), new LabelsAreCompatibleWithLearner($dataset, $this)])->check();
        $classes = $dataset->possibleOutcomes();
        $this->network = new FeedForward(new Placeholder1D($dataset->numFeatures()), [new Dense(count($classes), $this->l2Penalty, \true, new Xavier1())], new Multiclass($classes, $this->costFn), $this->optimizer);
        $this->network->initialize();
        $this->classes = $classes;
        $this->partial($dataset);
    }
    /**
     * Perform a partial train on the learner.
     *
     * @param \OCA\Recognize\Vendor\Rubix\ML\Datasets\Labeled $dataset
     */
    public function partial(Dataset $dataset) : void
    {
        if ($this->network == null) {
            $this->train($dataset);
            return;
        }
        SpecificationChain::with([new DatasetIsLabeled($dataset), new DatasetIsNotEmpty($dataset), new SamplesAreCompatibleWithEstimator($dataset, $this), new LabelsAreCompatibleWithLearner($dataset, $this)])->check();
        if ($this->logger) {
            $this->logger->info("Training {$this}");
            $numParams = number_format($this->network->numParams());
            $this->logger->info("{$numParams} trainable parameters");
        }
        $prevLoss = $bestLoss = \INF;
        $numWorseEpochs = 0;
        $this->losses = [];
        for ($epoch = 1; $epoch <= $this->epochs; ++$epoch) {
            $batches = $dataset->randomize()->batch($this->batchSize);
            $loss = 0.0;
            foreach ($batches as $batch) {
                $loss += $this->network->roundtrip($batch);
            }
            $loss /= count($batches);
            $lossChange = \abs($prevLoss - $loss);
            $this->losses[$epoch] = $loss;
            if ($this->logger) {
                $lossDirection = $loss < $prevLoss ? '↓' : '↑';
                $message = "Epoch: {$epoch}, " . "{$this->costFn}: {$loss}, " . "Loss Change: {$lossDirection}{$lossChange}";
                $this->logger->info($message);
            }
            if (is_nan($loss)) {
                if ($this->logger) {
                    $this->logger->warning('Numerical instability detected');
                }
                break;
            }
            if ($loss <= 0.0) {
                break;
            }
            if ($lossChange < $this->minChange) {
                break;
            }
            if ($loss < $bestLoss) {
                $bestLoss = $loss;
                $numWorseEpochs = 0;
            } else {
                ++$numWorseEpochs;
            }
            if ($numWorseEpochs >= $this->window) {
                break;
            }
            $prevLoss = $loss;
        }
        if ($this->logger) {
            $this->logger->info('Training complete');
        }
    }
    /**
     * Make predictions from a dataset.
     *
     * @param Dataset $dataset
     * @return list<string>
     */
    public function predict(Dataset $dataset) : array
    {
        return \array_map('OCA\\Recognize\\Vendor\\Rubix\\ML\\argmax', $this->proba($dataset));
    }
    /**
     * Estimate the joint probabilities for each possible outcome.
     *
     * @param Dataset $dataset
     * @throws RuntimeException
     * @return list<array<string,float>>
     */
    public function proba(Dataset $dataset) : array
    {
        if (!$this->network or !$this->classes) {
            throw new RuntimeException('Estimator has not been trained.');
        }
        DatasetHasDimensionality::with($dataset, $this->network->input()->width())->check();
        $activations = $this->network->infer($dataset);
        $probabilities = [];
        foreach ($activations->asArray() as $dist) {
            $probabilities[] = \array_combine($this->classes, $dist) ?: [];
        }
        return $probabilities;
    }
    /**
     * Return an associative array containing the data used to serialize the object.
     *
     * @return mixed[]
     */
    public function __serialize() : array
    {
        $properties = get_object_vars($this);
        unset($properties['losses']);
        return $properties;
    }
    /**
     * Return the string representation of the object.
     *
     * @internal
     *
     * @return string
     */
    public function __toString() : string
    {
        return 'Softmax Classifier (' . Params::stringify($this->params()) . ')';
    }
}
