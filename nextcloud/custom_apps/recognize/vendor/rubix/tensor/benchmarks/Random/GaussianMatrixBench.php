<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Random;

use OCA\Recognize\Vendor\Tensor\Matrix;
/**
 * @Groups({"Random"})
 * @internal
 */
class GaussianMatrixBench
{
    /**
     * @Subject
     * @Iterations(5)
     * @OutputTimeUnit("seconds", precision=3)
     */
    public function gaussian() : void
    {
        Matrix::gaussian(500, 500);
    }
}
