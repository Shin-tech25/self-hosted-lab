<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Random;

use OCA\Recognize\Vendor\Tensor\Matrix;
/**
 * @Groups({"Random"})
 * @internal
 */
class RandMatrixBench
{
    /**
     * @Subject
     * @Iterations(5)
     * @OutputTimeUnit("seconds", precision=3)
     */
    public function rand() : void
    {
        Matrix::rand(500, 500);
    }
}
