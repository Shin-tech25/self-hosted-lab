<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Persistable;
use OCA\Recognize\Vendor\Rubix\ML\Strategies\Mean;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Strategies\Strategy;
use OCA\Recognize\Vendor\Rubix\ML\Strategies\KMostFrequent;
use OCA\Recognize\Vendor\Rubix\ML\Traits\AutotrackRevisions;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SamplesAreCompatibleWithTransformer;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use function is_null;
/**
 * Missing Data Imputer
 *
 * Missing Data Imputer replaces missing continuous (denoted by `NaN`) or categorical values
 * (denoted by special placeholder category) with a guess based on user-defined Strategy.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class MissingDataImputer implements Transformer, Stateful, Persistable
{
    use AutotrackRevisions;
    /**
     * The guessing strategy to use when imputing continuous values.
     *
     * @var Strategy
     */
    protected Strategy $continuous;
    /**
     * The guessing strategy to use when imputing categorical values.
     *
     * @var Strategy
     */
    protected Strategy $categorical;
    /**
     * The placeholder category that denotes missing values.
     *
     * @var string
     */
    protected string $categoricalPlaceholder;
    /**
     * The fitted guessing strategy for each feature column.
     *
     * @var list<\OCA\Recognize\Vendor\Rubix\ML\Strategies\Strategy>|null
     */
    protected ?array $strategies = null;
    /**
     * The data types of the fitted feature columns.
     *
     * @var list<\OCA\Recognize\Vendor\Rubix\ML\DataType>|null
     */
    protected ?array $types = null;
    /**
     * @param Strategy|null $continuous
     * @param Strategy|null $categorical
     * @param string $categoricalPlaceholder
     * @throws InvalidArgumentException
     */
    public function __construct(?Strategy $continuous = null, ?Strategy $categorical = null, string $categoricalPlaceholder = '?')
    {
        if ($continuous and !$continuous->type()->isContinuous()) {
            throw new InvalidArgumentException('Continuous strategy must' . ' be compatible with continuous data types.');
        }
        if ($categorical and !$categorical->type()->isCategorical()) {
            throw new InvalidArgumentException('Categorical strategy must' . ' be compatible with categorical data types.');
        }
        $this->continuous = $continuous ?? new Mean();
        $this->categorical = $categorical ?? new KMostFrequent(1);
        $this->categoricalPlaceholder = $categoricalPlaceholder;
    }
    /**
     * Return the data types that this transformer is compatible with.
     *
     * @internal
     *
     * @return list<\OCA\Recognize\Vendor\Rubix\ML\DataType>
     */
    public function compatibility() : array
    {
        return DataType::all();
    }
    /**
     * Is the transformer fitted?
     *
     * @return bool
     */
    public function fitted() : bool
    {
        return isset($this->strategies, $this->types);
    }
    /**
     * Fit the transformer to a dataset.
     *
     * @param Dataset $dataset
     * @throws InvalidArgumentException
     */
    public function fit(Dataset $dataset) : void
    {
        SamplesAreCompatibleWithTransformer::with($dataset, $this)->check();
        $this->strategies = $this->types = [];
        foreach ($dataset->featureTypes() as $column => $type) {
            $donors = [];
            switch ($type->code()) {
                case DataType::CONTINUOUS:
                    $strategy = clone $this->continuous;
                    foreach ($dataset->feature($column) as $value) {
                        if (\is_float($value) and \is_nan($value)) {
                            continue;
                        }
                        $donors[] = $value;
                    }
                    break;
                case DataType::CATEGORICAL:
                    $strategy = clone $this->categorical;
                    foreach ($dataset->feature($column) as $value) {
                        if ($value !== $this->categoricalPlaceholder) {
                            $donors[] = $value;
                        }
                    }
                    break;
            }
            if (!isset($strategy)) {
                continue;
            }
            $strategy->fit($donors);
            $this->strategies[$column] = $strategy;
            $this->types[$column] = $type;
        }
    }
    /**
     * Transform the dataset in place.
     *
     * @param list<list<mixed>> $samples
     * @throws RuntimeException
     */
    public function transform(array &$samples) : void
    {
        if (is_null($this->strategies) or is_null($this->types)) {
            throw new RuntimeException('Transformer has not been fitted.');
        }
        foreach ($samples as &$sample) {
            foreach ($this->types as $column => $type) {
                $value =& $sample[$column];
                switch ($type->code()) {
                    case DataType::CONTINUOUS:
                        if (\is_float($value) and \is_nan($value)) {
                            $value = $this->strategies[$column]->guess();
                        }
                        break;
                    case DataType::CATEGORICAL:
                        if ($value === $this->categoricalPlaceholder) {
                            $value = $this->strategies[$column]->guess();
                        }
                        break;
                }
            }
        }
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
        return "Missing Data Imputer (continuous strategy: {$this->continuous}," . " categorical strategy: {$this->categorical}," . " categorical placeholder: {$this->categoricalPlaceholder})";
    }
}
