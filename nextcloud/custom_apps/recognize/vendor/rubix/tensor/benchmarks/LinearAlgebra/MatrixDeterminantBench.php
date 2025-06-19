<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\LinearAlgebra;

use OCA\Recognize\Vendor\Tensor\Matrix;
/**
 * @Groups({"LinearAlgebra"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class MatrixDeterminantBench
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
    public function det() : void
    {
        $this->a->det();
    }
}
