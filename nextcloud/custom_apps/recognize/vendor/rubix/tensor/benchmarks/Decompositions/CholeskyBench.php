<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Decompositions;

use OCA\Recognize\Vendor\Tensor\Matrix;
/**
 * @Groups({"Decompositions"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class CholeskyBench
{
    /**
     * @var Matrix
     */
    protected $a;
    public function setUp() : void
    {
        $this->a = Matrix::rand(500, 500);
    }
    /**
     * @Skip
     * @Subject
     * @Iterations(5)
     * @OutputTimeUnit("seconds", precision=3)
     */
    public function cholesky() : void
    {
        $this->a->cholesky();
    }
}
