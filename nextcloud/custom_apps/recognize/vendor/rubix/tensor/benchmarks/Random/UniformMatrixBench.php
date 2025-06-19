<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Random;

use OCA\Recognize\Vendor\Tensor\Matrix;
/**
 * @Groups({"Random"})
 * @internal
 */
class UniformMatrixBench
{
    /**
     * @Subject
     * @Iterations(5)
     * @OutputTimeUnit("seconds", precision=3)
     */
    public function uniform() : void
    {
        Matrix::uniform(500, 500);
    }
}
