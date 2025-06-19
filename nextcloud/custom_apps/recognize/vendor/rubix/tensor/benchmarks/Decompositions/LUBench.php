<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Decompositions;

use OCA\Recognize\Vendor\Tensor\Matrix;
/**
 * @Groups({"Decompositions"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class LUBench
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
    public function lu() : void
    {
        $this->a->lu();
    }
}
