<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Trigonometric;

use OCA\Recognize\Vendor\Tensor\Matrix;
/**
 * @Groups({"Trigonometric"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class SinMatrixBench
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
    public function sine() : void
    {
        $this->a->sin();
    }
}
