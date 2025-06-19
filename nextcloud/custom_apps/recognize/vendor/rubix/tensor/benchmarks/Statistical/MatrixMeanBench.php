<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Statistical;

use OCA\Recognize\Vendor\Tensor\Matrix;
/**
 * @Groups({"Statistical"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class MatrixMeanBench
{
    /**
     * @var Matrix
     */
    protected $a;
    public function setUp() : void
    {
        $this->a = Matrix::uniform(1000, 1000);
    }
    /**
     * @Subject
     * @Iterations(5)
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function mean() : void
    {
        $this->a->mean();
    }
}
