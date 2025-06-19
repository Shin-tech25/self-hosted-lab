<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Functions;

use OCA\Recognize\Vendor\Tensor\Matrix;
/**
 * @Groups({"Functions"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class SqrtMatrixBench
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
    public function sqrt() : void
    {
        $this->a->sqrt();
    }
}
