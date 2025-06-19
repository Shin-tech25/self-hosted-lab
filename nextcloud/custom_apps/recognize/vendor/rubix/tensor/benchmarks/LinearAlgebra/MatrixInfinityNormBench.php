<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\LinearAlgebra;

use OCA\Recognize\Vendor\Tensor\Matrix;
/**
 * @Groups({"LinearAlgebra"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class MatrixInfinityNormBench
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
    public function infinityNorm() : void
    {
        $this->a->infinityNorm();
    }
}
