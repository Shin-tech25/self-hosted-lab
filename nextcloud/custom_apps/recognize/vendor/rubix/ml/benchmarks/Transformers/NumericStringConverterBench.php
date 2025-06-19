<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Benchmarks\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Generators\Blob;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\LambdaFunction;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\NumericStringConverter;
/**
 * @Groups({"Transformers"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class NumericStringConverterBench
{
    protected const DATASET_SIZE = 100000;
    /**
     * @var \OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset
     */
    public $dataset;
    /**
     * @var NumericStringConverter
     */
    protected $transformer;
    public function setUp() : void
    {
        $generator = new Blob([0.0, 0.0, 0.0, 0.0]);
        $this->dataset = $generator->generate(self::DATASET_SIZE)->apply(new LambdaFunction(function (&$sample) {
            $sample[1] = \strval($sample[1]);
            $sample[3] = \strval($sample[3]);
        }));
        $this->transformer = new NumericStringConverter();
    }
    /**
     * @Subject
     * @Iterations(5)
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function apply() : void
    {
        $this->dataset->apply($this->transformer);
    }
}
