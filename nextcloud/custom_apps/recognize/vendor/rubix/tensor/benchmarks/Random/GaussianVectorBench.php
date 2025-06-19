<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Random;

use OCA\Recognize\Vendor\Tensor\Vector;
/**
 * @Groups({"Random"})
 * @internal
 */
class GaussianMVectorBench
{
    /**
     * @Subject
     * @Iterations(5)
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function gaussian() : void
    {
        Vector::gaussian(100000);
    }
}
