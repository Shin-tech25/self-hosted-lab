<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Arithmetic;

use OCA\Recognize\Vendor\Tensor\Matrix;
/**
 * @Groups({"Arithmetic"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class MatrixSumBench
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
     * @OutputTimeUnit("seconds", precision=3)
     */
    public function sum() : void
    {
        $this->a->sum();
    }
}
