<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Benchmarks\Transformers;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\TfIdfTransformer;
/**
 * @Groups({"Transformers"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class TfIdfTransformerBench
{
    protected const DATASET_SIZE = 10000;
    /**
     * @var Unlabeled
     */
    public $dataset;
    /**
     * @var TfIdfTransformer
     */
    protected $transformer;
    public function setUp() : void
    {
        $mask = Matrix::rand(self::DATASET_SIZE, 4)->greater(0.8);
        $samples = Matrix::gaussian(self::DATASET_SIZE, 4)->multiply($mask)->asArray();
        $this->dataset = Unlabeled::quick($samples);
        $this->transformer = new TfIdfTransformer();
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
