<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Arithmetic;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Tensor\Vector;
/**
 * @Groups({"Arithmetic"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class MatrixVectorMultiplyBench
{
    /**
     * @var Matrix
     */
    protected $a;
    /**
     * @var Vector
     */
    protected $b;
    public function setUp() : void
    {
        $this->a = Matrix::uniform(1000, 1000);
        $this->b = Vector::uniform(1000);
    }
    /**
     * @Subject
     * @Iterations(5)
     * @OutputTimeUnit("seconds", precision=3)
     */
    public function multiply() : void
    {
        $this->a->multiply($this->b);
    }
}
