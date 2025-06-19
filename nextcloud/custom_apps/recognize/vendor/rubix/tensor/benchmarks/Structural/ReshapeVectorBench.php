<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Structural;

use OCA\Recognize\Vendor\Tensor\Vector;
/**
 * @Groups({"Structural"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class ReshapeVectorBench
{
    /**
     * @var Vector
     */
    protected $a;
    public function setUp() : void
    {
        $this->a = Vector::uniform(250000);
    }
    /**
     * @Subject
     * @Iterations(5)
     * @OutputTimeUnit("seconds", precision=3)
     */
    public function reshape() : void
    {
        $this->a->reshape(500, 500);
    }
}
