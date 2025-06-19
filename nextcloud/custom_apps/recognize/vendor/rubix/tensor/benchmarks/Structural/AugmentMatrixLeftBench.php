<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Structural;

use OCA\Recognize\Vendor\Tensor\Matrix;
/**
 * @Groups({"Structural"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class AugmentMatrixLeftBench
{
    /**
     * @var Matrix
     */
    protected $a;
    /**
     * @var Matrix
     */
    protected $b;
    public function setUp() : void
    {
        $this->a = Matrix::uniform(500, 500);
        $this->b = Matrix::uniform(500, 500);
    }
    /**
     * @Subject
     * @Iterations(5)
     * @OutputTimeUnit("seconds", precision=3)
     */
    public function augmentLeft() : void
    {
        $this->a->augmentLeft($this->b);
    }
}
