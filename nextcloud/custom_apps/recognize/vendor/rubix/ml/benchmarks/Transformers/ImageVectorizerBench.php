<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Benchmarks\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\ImageVectorizer;
/**
 * @Groups({"Transformers"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class ImageVectorizerBench
{
    protected const DATASET_SIZE = 1000;
    /**
     * @var \OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset
     */
    public $dataset;
    /**
     * @var ImageVectorizer
     */
    protected $transformer;
    public function setUp() : void
    {
        $samples = [];
        for ($i = 0; $i < self::DATASET_SIZE; ++$i) {
            $samples[] = [\imagecreatefrompng('tests/test.png')];
        }
        $this->dataset = Unlabeled::build($samples);
        $this->transformer = new ImageVectorizer();
    }
    /**
     * @Subject
     * @Iterations(5)
     * @OutputTimeUnit("seconds", precision=3)
     */
    public function apply() : void
    {
        $this->dataset->apply($this->transformer);
    }
}
