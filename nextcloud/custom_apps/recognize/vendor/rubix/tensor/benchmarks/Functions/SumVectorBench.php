<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Functions;

use OCA\Recognize\Vendor\Tensor\Vector;
/**
 * @Groups({"Functions"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class SumVectorBench
{
    /**
     * @var Vector
     */
    protected $a;
    public function setUp() : void
    {
        $this->a = Vector::uniform(100000);
    }
    /**
     * @Subject
     * @Iterations(5)
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function sum() : void
    {
        $this->a->sum();
    }
}
