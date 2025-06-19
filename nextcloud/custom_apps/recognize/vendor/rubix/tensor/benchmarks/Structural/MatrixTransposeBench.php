<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Structural;

use OCA\Recognize\Vendor\Tensor\Matrix;
/**
 * @Groups({"Structural"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class MatrixTransposeBench
{
    /**
     * @var Matrix
     */
    protected $a;
    public function setUp() : void
    {
        $this->a = Matrix::uniform(1000, 1000);
    }
    /**
     * @Subject
     * @Iterations(5)
     * @OutputTimeUnit("seconds", precision=3)
     */
    public function transpose() : void
    {
        $this->a->transpose();
    }
}
