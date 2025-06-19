<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Random;

use OCA\Recognize\Vendor\Tensor\Matrix;
/**
 * @Groups({"Random"})
 * @internal
 */
class PoissonMatrixBench
{
    /**
     * @Subject
     * @Iterations(5)
     * @OutputTimeUnit("seconds", precision=3)
     */
    public function poisson() : void
    {
        Matrix::poisson(500, 500);
    }
}
