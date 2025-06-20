<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Benchmarks\Transformers;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\BM25Transformer;
/**
 * @Groups({"Transformers"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class BM25TransformerBench
{
    protected const NUM_SAMPLES = 10000;
    /**
     * @var Unlabeled
     */
    protected $dataset;
    /**
     * @var BM25Transformer
     */
    protected $transformer;
    /**
     * @var array<array<mixed>>
     */
    protected $aSamples;
    /**
     * @var array<array<mixed>>
     */
    protected $bSamples;
    public function setUp() : void
    {
        $mask = Matrix::rand(self::NUM_SAMPLES, 100)->greater(0.8);
        $samples = Matrix::gaussian(self::NUM_SAMPLES, 100)->multiply($mask)->asArray();
        $this->dataset = Unlabeled::quick($samples);
        $this->transformer = new BM25Transformer();
    }
    /**
     * @Subject
     * @Iterations(3)
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function apply() : void
    {
        $this->dataset->apply($this->transformer);
    }
}
