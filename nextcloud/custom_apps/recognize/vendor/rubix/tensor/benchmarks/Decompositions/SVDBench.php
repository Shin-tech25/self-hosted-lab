<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Decompositions;

use OCA\Recognize\Vendor\Tensor\Matrix;
/**
 * @Groups({"Decompositions"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class SVDBench
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
    public function eig() : void
    {
        $this->a->svd();
    }
}
