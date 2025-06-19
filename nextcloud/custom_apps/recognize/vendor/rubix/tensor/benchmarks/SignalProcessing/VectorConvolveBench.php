<?php

namespace OCA\Recognize\Vendor\Tensor\Benchmarks\Special;

use OCA\Recognize\Vendor\Tensor\Vector;
/**
 * @Groups({"Signal Processing"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class VectorConvolveBench
{
    /**
     * @var Vector
     */
    protected $a;
    /**
     * @var Vector
     */
    protected $kernel;
    public function setUp() : void
    {
        $this->a = Vector::uniform(250000);
        $this->kernel = Vector::uniform(100);
    }
    /**
     * @Subject
     * @Iterations(5)
     * @OutputTimeUnit("seconds", precision=3)
     */
    public function convolve() : void
    {
        $this->a->convolve($this->kernel);
    }
}
