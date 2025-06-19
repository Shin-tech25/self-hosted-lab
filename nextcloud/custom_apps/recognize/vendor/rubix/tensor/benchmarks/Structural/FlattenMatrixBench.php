<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Structural;

use OCA\Recognize\Vendor\Tensor\Matrix;
/**
 * @Groups({"Structural"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class FlattenMatrixBench
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
    public function flatten() : void
    {
        $this->a->flatten();
    }
}
