<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Reductions;

use OCA\Recognize\Vendor\Tensor\Matrix;
/**
 * @Groups({"Reductions"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class RREFBench
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
    public function rref() : void
    {
        $this->a->rref();
    }
}
