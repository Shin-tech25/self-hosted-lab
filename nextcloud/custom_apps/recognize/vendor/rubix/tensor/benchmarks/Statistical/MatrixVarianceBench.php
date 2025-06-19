<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Statistical;

use OCA\Recognize\Vendor\Tensor\Matrix;
/**
 * @Groups({"Statistical"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class MatrixVarianceBench
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
    public function variance() : void
    {
        $this->a->variance();
    }
}
