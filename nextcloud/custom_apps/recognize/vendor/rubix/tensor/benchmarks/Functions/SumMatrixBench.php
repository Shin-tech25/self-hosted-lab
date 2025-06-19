<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Functions;

use OCA\Recognize\Vendor\Tensor\Matrix;
/**
 * @Groups({"Functions"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class SumMatrixBench
{
    /**
     * @var Matrix
     */
    protected $a;
    public function setUp() : void
    {
        $this->a = Matrix::uniform(500, 500);
    }
    /**
     * @Subject
     * @Iterations(5)
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function sum() : void
    {
        $this->a->sum();
    }
}
